<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('internship_submissions', function (AlterBuilder $table) {
      $table->varchar('original_file_name', 255)->nullable();
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('internship_submissions', function (AlterBuilder $table) {
      $table->dropColumn('original_file_name');
    });
  }
};
