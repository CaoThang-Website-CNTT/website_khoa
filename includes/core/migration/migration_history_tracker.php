<?php
namespace App\Core\Migration;

use Database;
use PDO;

class MigrationHistoryTracker
{
  protected PDO $db;
  protected string $table = 'migrations';

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }

  /**
   * Tạo bảng migrations nếu chưa tồn tại (Khởi tạo hệ thống)
   */
  public function ensureTableExists(): void
  {
    $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT NOT NULL,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;";

    $this->db->exec($sql);
  }

  /**
   * Lấy danh sách các file đã chạy
   */
  public function getRanMigrations(): array
  {
    $stmt = $this->db->query("SELECT migration FROM `{$this->table}`");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  /**
   * Lấy danh sách các migration thuộc Batch cuối cùng (phục vụ rollback mặc định)
   * Trả về mảng các bản ghi chứa 'migration' và 'batch'
   */
  public function getLastBatch(): array
  {
    $sql = "SELECT migration, batch 
            FROM `{$this->table}` 
            WHERE batch = (SELECT MAX(batch) FROM `{$this->table}`) 
            ORDER BY id DESC";

    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Lấy toàn bộ danh sách migration theo thứ tự ngược lại (phục vụ rollback --all)
   */
  public function getAllInReverse(): array
  {
    $stmt = $this->db->query("SELECT migration, batch FROM `{$this->table}` ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Lưu lại một file vừa chạy thành công
   */
  public function log(string $migration, int $batch): void
  {
    $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (migration, batch) VALUES (?, ?)");
    $stmt->execute([$migration, $batch]);
  }

  /**
   * Lấy số Batch tiếp theo
   */
  public function getNextBatchNumber(): int
  {
    $stmt = $this->db->query("SELECT MAX(batch) FROM `{$this->table}`");
    $max = $stmt->fetchColumn();
    return ($max !== null) ? (int) $max + 1 : 1;
  }

  /**
   * Lấy danh sách migration để rollback dựa trên số bước (steps)
   * Nếu $steps = 0, mặc định sẽ lấy toàn bộ migration của BATCH cuối cùng.
   */
  public function getMigrationsToRollback(int $steps = 0): array
  {
    if ($steps > 0) {
      // Lấy chính xác n file gần nhất
      $sql = "SELECT migration, batch FROM `{$this->table}` ORDER BY id DESC LIMIT {$steps}";
    } else {
      // Lấy tất cả file thuộc Batch lớn nhất
      $sql = "SELECT migration, batch FROM `{$this->table}` 
                WHERE batch = (SELECT MAX(batch) FROM `{$this->table}`) 
                ORDER BY id DESC";
    }

    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Xóa log khi Rollback
   */
  public function remove(string $migration): void
  {
    $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE migration = ?");
    $stmt->execute([$migration]);
  }
}