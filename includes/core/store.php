<?php
namespace App\Core;

require_once BASE_PATH . '/includes/core/database.php';

use App\Core\Schema\IQueryBuilder;
use Database;
use PDO;

abstract class Store
{
  protected PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }

  /**
   * Tự động áp dụng mảng bộ lọc từ URL vào QueryBuilder
   * * @param IQueryBuilder $builder
   * @param array|null $filters Mảng filter nhận từ $request->query('filter')
   * @param array $overrideOperators Danh sách toán tử bổ sung hoặc ghi đè
   * @return IQueryBuilder
   */
  protected function applyFilters(IQueryBuilder $builder, ?array $filters, array $overrideOperators = []): IQueryBuilder
  {
    if (empty($filters) || !is_array($filters)) {
      return $builder;
    }

    // Danh sách toán tử mặc định khớp với các method của IQueryBuilder
    $defaultOperators = ['eq', 'neq', 'gt', 'lt', 'like', 'ilike', 'is', 'in'];

    // Gộp hoặc ghi đè danh sách toán tử nếu lập trình viên truyền vào tùy biến riêng
    $allowedOperators = !empty($overrideOperators) ? $overrideOperators : $defaultOperators;

    foreach ($filters as $column => $operators) {
      if (is_array($operators)) {
        foreach ($operators as $operator => $value) {
          // Kiểm tra tính hợp lệ của toán tử và sự tồn tại của method trên Builder
          if (in_array($operator, $allowedOperators) && method_exists($builder, $operator)) {
            // Cast loại dữ liệu an toàn cho column trước khi truyền vào builder
            $builder->$operator((string) $column, $value);
          }
        }
      }
    }

    return $builder;
  }
}