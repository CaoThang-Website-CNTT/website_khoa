<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('internship_batch_students', function (AlterBuilder $table) {
      $table->bigInt('company_id')->unsigned()->nullable();
      $table->varchar('position', 255)->nullable();
      $table->date('internship_start_date')->nullable();
      $table->date('internship_end_date')->nullable();
      $table->addForeign('company_id')->references('id')->on('companies')->onDelete('set null');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('internship_batch_students', function (AlterBuilder $table) {
      $table->dropForeign('fk_internship_batch_students_company_id');
      $table->dropColumn(['company_id', 'position', 'internship_start_date', 'internship_end_date']);
    });
  }
};
