<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('accounts', function (AlterBuilder $table) {
      $table->modifyColumn(function ($t) {
        $t->enum('role', ['student', 'admin', 'teacher', 'editor', 'super_admin']);
      });
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->alter('accounts', function (AlterBuilder $table) {
      $table->modifyColumn(function ($t) {
        $t->enum('role', ['student', 'admin', 'teacher']);
      });
    });

    $schema->enableForeignKeys();
  }
};
