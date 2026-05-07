<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $db = Database::getInstance()->getConnection();
    
    // Kiểm tra xem cột đã tồn tại chưa để tránh lỗi
    $columns = $db->query("SHOW COLUMNS FROM internship_batch_students LIKE 'company_id'")->fetch();
    
    if (!$columns) {
      $db->exec("ALTER TABLE internship_batch_students 
                 ADD COLUMN company_id BIGINT UNSIGNED NULL, 
                 ADD COLUMN position VARCHAR(255) NULL, 
                 ADD COLUMN internship_start_date DATE NULL, 
                 ADD COLUMN internship_end_date DATE NULL, 
                 ADD CONSTRAINT fk_ibs_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL;");
    }
  }

  public function back(TableBuilder $schema): void
  {
    $db = Database::getInstance()->getConnection();
    
    // Kiểm tra foreign key tồn tại trước khi xóa
    try {
      $db->exec("ALTER TABLE internship_batch_students DROP FOREIGN KEY fk_ibs_company");
    } catch (\Exception $e) {}

    $db->exec("ALTER TABLE internship_batch_students 
               DROP COLUMN IF EXISTS company_id,
               DROP COLUMN IF EXISTS position,
               DROP COLUMN IF EXISTS internship_start_date,
               DROP COLUMN IF EXISTS internship_end_date;");
  }
};
