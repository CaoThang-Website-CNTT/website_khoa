<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $db = Database::getInstance()->getConnection();
    
    $check = $db->query("SHOW COLUMNS FROM companies LIKE 'is_verified'")->fetch();
    if (!$check) {
      $db->exec("ALTER TABLE companies 
                 ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER note,
                 ADD COLUMN source ENUM('api', 'manual') DEFAULT 'api' AFTER is_verified;");
    }
  }

  public function back(TableBuilder $schema): void
  {
    $db = Database::getInstance()->getConnection();
    $db->exec("ALTER TABLE companies 
               DROP COLUMN IF EXISTS source,
               DROP COLUMN IF EXISTS is_verified;");
  }
};
