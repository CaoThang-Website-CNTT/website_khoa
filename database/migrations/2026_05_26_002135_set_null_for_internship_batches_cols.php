<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration
{
  public function forward(TableBuilder $schema): void
  {
    $db = Database::getInstance()->getConnection();


    $db->exec("ALTER TABLE internship_batches 
                MODIFY COLUMN class_of INT NULL COMMENT 'Nien khoa, VD: 23',
                MODIFY COLUMN level ENUM('CĐ', 'CĐN') NULL COMMENT 'Bac hoc';");
  }
  public function back(TableBuilder $schema): void {}
};
