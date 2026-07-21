<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;
use App\Core\Schema\AlterBuilder;

return new class extends BaseMigration
{
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('internship_grades', function (AlterBuilder $table) {
      $table->boolean('has_submitted_hardcopy')->default(0)->after('batch_student_id');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('internship_grades', function (AlterBuilder $table) {
      $table->dropColumn('has_submitted_hardcopy');
    });
  }
};
