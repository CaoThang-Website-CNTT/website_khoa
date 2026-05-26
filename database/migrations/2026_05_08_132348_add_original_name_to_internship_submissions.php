<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration
{
  public function forward(TableBuilder $schema): void
  {
    $db = Database::getInstance()->getConnection();

    // Kiểm tra xem cột đã tồn tại chưa để tránh lỗi
    $columns = $db->query("SHOW COLUMNS FROM internship_submissions LIKE 'original_file_name'")->fetch();

    if (!$columns) {
      $db->exec("ALTER TABLE internship_submissions 
                 ADD COLUMN original_file_name varchar(255) NULL;");
    }
  }
  public function back(TableBuilder $schema): void
  {
    $db = Database::getInstance()->getConnection();

    $db->exec("ALTER TABLE internship_submissions 
               DROP COLUMN IF EXISTS original_file_name;");
  }
};
