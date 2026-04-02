<?php
namespace App\Core\Schema;

use App\Core\Schema\Compiler\ISQLCompiler;

interface IQueryBuilder
{
  public function insert(array $data): static;
  public function update(array $data): static;
  /** * Xác định bảng chính để truy vấn dữ liệu.
   */
  public function from(string $table): static;

  /** * Chọn các cột cần lấy dữ liệu (mặc định là *).
   */
  public function select(string|array ...$columns): static;

  /** * Thêm điều kiện so sánh bằng (=).
   */
  public function eq(string $column, mixed $value): static;

  /** * Thêm điều kiện so sánh không bằng (<>).
   */
  public function neq(string $column, mixed $value): static;

  /** * Thêm điều kiện so sánh lớn hơn (>).
   */
  public function gt(string $column, mixed $value): static;

  /** * Thêm điều kiện so sánh nhỏ hơn (<).
   */
  public function lt(string $column, mixed $value): static;

  /** * Tìm kiếm theo mẫu (Sử dụng toán tử LIKE).
   */
  public function like(string $column, string $pattern): static;

  /** * Thêm điều kiện kiểm tra giá trị (Ví dụ: IS NULL, IS NOT NULL).
   */
  public function is(string $column, mixed $value): static;

  /** * Thêm điều kiện kiểm tra thuộc một danh sách giá trị (IN).
   */
  public function in(string $column, array $values): static;

  /** * Thực hiện liên kết (JOIN) với một bảng khác.
   */
  public function join(string $table, string $first, string $operator, string $second, string $type = 'inner'): static;

  /** * Sắp xếp kết quả theo cột và hướng (tăng/giảm).
   */
  public function order(string $column, array $options = ['ascending' => true]): static;

  /** * Giới hạn số lượng bản ghi trả về.
   */
  public function limit(int $count): static;

  /** * Lấy một khoảng bản ghi (sử dụng cho phân trang).
   */
  public function range(int $from, int $to): static;

  /** * Biên dịch cấu trúc hiện tại thành câu lệnh SQL thuần.
   */
  public function toSql(): string;

  /** * Lấy danh sách các giá trị binding để chuẩn bị cho PreparedStatement.
   */
  public function getBindings(): array;
}

class QueryBuilder implements IQueryBuilder
{
  protected ISQLCompiler $compiler;
  protected array $insertData = [];
  protected array $updateData = [];
  protected string $table = '';
  protected array $columns = ['*'];
  protected array $wheres = [];
  protected array $joins = [];
  protected array $orders = [];
  protected ?int $limit = null;
  protected ?int $offset = null;
  protected array $bindings = [];
  protected array $whereBindings = [];

  public function __construct(ISQLCompiler $compiler)
  {
    $this->compiler = $compiler;
  }
  public function insert(array $data): static
  {
    $this->insertData = $data;
    return $this;
  }

  public function update(array $data): static
  {
    $this->updateData = $data;
    return $this;
  }

  public function from(string $table): static
  {
    $this->table = $table;
    return $this;
  }

  public function select(string|array ...$columns): static
  {
    if (empty($columns))
      $this->columns = ['*'];
    elseif (is_array($columns[0]))
      $this->columns = $columns[0];
    elseif (count($columns) === 1 && str_contains($columns[0], ','))
      $this->columns = array_map('trim', explode(',', $columns[0]));
    else
      $this->columns = $columns;
    return $this;
  }

  public function eq(string $column, mixed $value): static
  {
    return $this->addWhere($column, '=', $value);
  }
  public function neq(string $column, mixed $value): static
  {
    return $this->addWhere($column, '<>', $value);
  }
  public function gt(string $column, mixed $value): static
  {
    return $this->addWhere($column, '>', $value);
  }
  public function lt(string $column, mixed $value): static
  {
    return $this->addWhere($column, '<', $value);
  }
  public function like(string $column, string $pattern): static
  {
    return $this->addWhere($column, 'LIKE', $pattern);
  }
  public function ilike(string $column, string $pattern): static
  {
    return $this->addWhere($column, 'ILIKE', $pattern);
  }

  public function is(string $column, mixed $value): static
  {
    $this->wheres[] = ['type' => 'Null', 'column' => $column, 'operator' => $value === null ? 'IS NULL' : "IS $value", 'boolean' => 'AND'];
    return $this;
  }

  public function in(string $column, array $values): static
  {
    $this->wheres[] = ['type' => 'In', 'column' => $column, 'values' => $values, 'boolean' => 'AND'];
    $this->whereBindings = array_merge($this->whereBindings, $values);
    return $this;
  }

  public function join(string $table, string $first, string $operator, string $second, string $type = 'inner'): static
  {
    $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
    return $this;
  }

  public function leftJoin(string $table, string $first, string $operator, string $second): static
  {
    return $this->join($table, $first, $operator, $second, 'left');
  }

  protected function addWhere(string $column, string $operator, mixed $value, string $boolean = 'AND'): static
  {
    $this->wheres[] = ['type' => 'Basic', 'column' => $column, 'operator' => $operator, 'boolean' => $boolean];
    $this->whereBindings[] = $value; // tách riêng
    return $this;
  }
  public function order(string $column, array $options = ['ascending' => true]): static
  {
    $this->orders[] = ['column' => $column, 'direction' => ($options['ascending'] ?? true) ? 'ASC' : 'DESC'];
    return $this;
  }

  public function limit(int $count): static
  {
    $this->limit = $count;
    return $this;
  }
  public function range(int $from, int $to): static
  {
    $this->limit = $to - $from + 1;
    $this->offset = $from;
    return $this;
  }

  public function toSql(): string
  {
    if (!empty($this->insertData)) {
      return $this->compiler->compileInsert($this->table, $this->insertData);
    }
    if (!empty($this->updateData)) {
      return $this->compiler->compileUpdate($this->table, $this->updateData, $this->wheres);
    }
    return $this->compiler->compileSelect(
      $this->table,
      $this->columns,
      $this->wheres,
      $this->joins,
      $this->orders,
      $this->limit,
      $this->offset
    );
  }

  public function getBindings(): array
  {
    if (!empty($this->insertData)) {
      return array_values($this->insertData);
    }
    if (!empty($this->updateData)) {
      // SET values trước, WHERE values sau — đúng thứ tự prepared statement
      return array_merge(array_values($this->updateData), $this->whereBindings);
    }
    return $this->whereBindings; // SELECT
  }
}