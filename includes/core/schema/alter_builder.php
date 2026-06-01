<?php
namespace App\Core\Schema;

class AlterBuilder
{
  use ColumnTypeTrait;

  protected string $tableName;

  /** @var AlterOperation[] */
  protected array $operations = [];

  /**
   * Pending column context - dùng nội bộ khi trait gọi addColumn().
   * Xác định operation type cho lần addColumn() tiếp theo.
   * Mặc định là ADD_COLUMN.
   */
  private string $pendingOperationType = AlterOperation::ADD_COLUMN;

  public function __construct(string $tableName)
  {
    $this->tableName = $tableName;
  }

  // ─── Core hook cho ColumnTypeTrait ───────────────────────────────────────

  /**
   * Override addColumn() từ trait.
   * Thay vì push vào $columns[], tạo AlterOperation với type đang pending.
   *
   * Trait gọi addColumn() → tạo ColumnDefinition → trả về để chain fluent.
   * AlterOperation được tạo ngay tại đây vì ColumnDefinition là reference type -
   * mọi fluent call sau đó (.nullable(), .after(), ...) vẫn mutate đúng object.
   */
  protected function addColumn(string $type, string $name, array $params = []): ColumnDefinition
  {
    $col = new ColumnDefinition($name, $type, $params);
    $this->operations[] = new AlterOperation($this->pendingOperationType, $col);

    // Reset về ADD_COLUMN sau mỗi lần dùng
    $this->pendingOperationType = AlterOperation::ADD_COLUMN;

    return $col;
  }

  // ─── ADD COLUMN ───────────────────────────────────────────────────────────

  /**
   * Thêm cột mới vào bảng.
   * Gọi các type method trực tiếp trên builder: $table->varchar('phone', 20)->after('email')
   *
   * @example
   * $table->varchar('phone', 20)->nullable()->after('email');
   * $table->boolean('is_active')->default(1)->first();
   */

  // ADD COLUMN được handle tự động qua ColumnTypeTrait.
  // pendingOperationType mặc định là ADD_COLUMN nên không cần method wrapper.

  // ─── MODIFY COLUMN ────────────────────────────────────────────────────────

  /**
   * Đổi type/constraint của cột hiện có, giữ nguyên tên.
   * Compiler sẽ emit: MODIFY COLUMN `col` <new definition>
   *
   * @example
   * $table->modifyColumn(fn($t) => $t->varchar('email', 320)->comment('Updated'));
   * $table->modifyColumn(fn($t) => $t->int('score')->unsigned()->default(0));
   */
  public function modifyColumn(callable $callback): self
  {
    $this->pendingOperationType = AlterOperation::MODIFY_COLUMN;
    $callback($this);
    return $this;
  }

  // ─── RENAME COLUMN ────────────────────────────────────────────────────────

  /**
   * Đổi tên cột, đồng thời có thể khai báo lại definition.
   * MySQL: CHANGE COLUMN `old` `new` <type>  - BẮT BUỘC phải có full definition.
   * PostgreSQL: có thể tách thành 2 statement (compiler tự xử lý).
   *
   * Nếu không truyền callback, compiler sẽ cần tự resolve definition từ schema
   * hiện tại (nằm ngoài scope của builder). Để đơn giản và explicit, callback là
   * bắt buộc - người dùng phải khai báo lại type khi rename.
   *
   * @example
   * $table->renameColumn('username', 'display_name', fn($t) => $t->varchar('display_name', 100));
   */
  public function renameColumn(string $from, string $to, callable $callback): self
  {
    // Dùng một sub-builder tạm để capture ColumnDefinition từ callback
    $proxy = new self($this->tableName);
    $callback($proxy);

    $lastOp = end($proxy->operations);
    $colDef = $lastOp ? $lastOp->payload : null;

    if (!$colDef instanceof ColumnDefinition) {
      throw new \LogicException(
        "[AlterBuilder] renameColumn('{$from}', '{$to}'): callback phải khai báo đúng một column type."
      );
    }

    // Override name trong ColumnDefinition thành tên đích
    $colDef->name = $to;

    $this->operations[] = new AlterOperation(AlterOperation::RENAME_COLUMN, [
      'from' => $from,
      'to' => $to,
      'definition' => $colDef,
    ]);

    return $this;
  }

  // ─── DROP COLUMN ─────────────────────────────────────────────────────────

  /**
   * Xóa cột khỏi bảng.
   *
   * @example
   * $table->dropColumn('legacy_field');
   * $table->dropColumn(['field_a', 'field_b']); // Batch drop
   */
  public function dropColumn(string|array $columns): self
  {
    foreach ((array) $columns as $col) {
      $this->operations[] = new AlterOperation(AlterOperation::DROP_COLUMN, $col);
    }
    return $this;
  }

  // ─── INDEX ────────────────────────────────────────────────────────────────

  /**
   * Thêm index thường (non-unique).
   *
   * @example
   * $table->addIndex('created_at');
   * $table->addIndex(['status', 'created_at'], 'idx_status_created');
   */
  public function addIndex(string|array $columns, ?string $name = null): self
  {
    $this->operations[] = new AlterOperation(AlterOperation::ADD_INDEX, [
      'columns' => (array) $columns,
      'name' => $name,
    ]);
    return $this;
  }

  /**
   * Xóa index theo tên.
   *
   * @example
   * $table->dropIndex('idx_status_created');
   */
  public function dropIndex(string $name): self
  {
    $this->operations[] = new AlterOperation(AlterOperation::DROP_INDEX, $name);
    return $this;
  }

  /**
   * Thêm unique index.
   *
   * @example
   * $table->addUnique('email');
   * $table->addUnique(['tenant_id', 'email'], 'uniq_tenant_email');
   */
  public function addUnique(string|array $columns, ?string $name = null): self
  {
    $this->operations[] = new AlterOperation(AlterOperation::ADD_UNIQUE, [
      'columns' => (array) $columns,
      'name' => $name,
    ]);
    return $this;
  }

  // ─── FOREIGN KEY ─────────────────────────────────────────────────────────

  /**
   * Thêm foreign key constraint.
   * Trả về ForeignDefinition để chain fluent.
   *
   * @example
   * $table->addForeign('user_id')->references('id')->on('users')->onDelete('cascade');
   */
  public function addForeign(string $column): ForeignDefinition
  {
    $fk = new ForeignDefinition($column);
    $this->operations[] = new AlterOperation(AlterOperation::ADD_FOREIGN, $fk);
    return $fk;
  }

  /**
   * Xóa foreign key constraint theo tên constraint.
   * Nếu không truyền tên, tự sinh theo convention: fk_{table}_{column}.
   *
   * @example
   * $table->dropForeign('fk_posts_user_id');
   * $table->dropForeign('user_id'); // Tự sinh tên: fk_{tableName}_user_id
   */
  public function dropForeign(string $constraintOrColumn): self
  {
    // Heuristic: nếu tên chưa có prefix "fk_", coi đây là tên cột và tự sinh tên constraint
    $name = str_starts_with($constraintOrColumn, 'fk_')
      ? $constraintOrColumn
      : "fk_{$this->tableName}_{$constraintOrColumn}";

    $this->operations[] = new AlterOperation(AlterOperation::DROP_FOREIGN, $name);
    return $this;
  }

  // ─── RENAME TABLE ─────────────────────────────────────────────────────────

  /**
   * Đổi tên bảng.
   *
   * @example
   * $table->renameTable('new_table_name');
   */
  public function renameTable(string $newName): self
  {
    $this->operations[] = new AlterOperation(AlterOperation::RENAME_TABLE, $newName);
    return $this;
  }

  // ─── Timestamps shorthand ─────────────────────────────────────────────────

  /**
   * Thêm cột created_at + updated_at nếu chưa có.
   * Dùng khi migrate bảng cũ chưa có timestamps.
   */
  public function addTimestamps(): self
  {
    $this->timestamp('created_at')->default('CURRENT_TIMESTAMP');
    $this->timestamp('updated_at')->default('CURRENT_TIMESTAMP')->onUpdate('CURRENT_TIMESTAMP');
    return $this;
  }

  // ─── Getters ─────────────────────────────────────────────────────────────

  public function getTable(): string
  {
    return $this->tableName;
  }

  /** @return AlterOperation[] */
  public function getOperations(): array
  {
    return $this->operations;
  }
}