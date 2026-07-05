<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('internship_batches', function (AlterBuilder $table) {
      $table->modifyColumn(fn(AlterBuilder $column) => $column->int('class_of')->nullable()->comment('Nien khoa, VD: 23'));
      $table->modifyColumn(fn(AlterBuilder $column) => $column->enum('level', ['CĐ', 'CĐN'])->nullable()->comment('Bac hoc'));
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('internship_batches', function (AlterBuilder $table) {
      $table->modifyColumn(fn(AlterBuilder $column) => $column->int('class_of')->comment('Nien khoa, VD: 23'));
      $table->modifyColumn(fn(AlterBuilder $column) => $column->enum('level', ['CĐ', 'CĐN'])->comment('Bac hoc'));
    });
  }
};
