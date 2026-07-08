<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->create('project_batch_eligible_students', function (TableBuilder $table) {
      $table->id();
      $table->bigInt('batch_id')->unsigned();
      $table->bigInt('student_id')->unsigned();
      $table->timestamp('created_at')->default('CURRENT_TIMESTAMP');
      
      $table->foreign('batch_id')->references('id')->on('project_batches')->onDelete('CASCADE');
      $table->foreign('student_id')->references('id')->on('students')->onDelete('CASCADE');
      $table->unique(['batch_id', 'student_id']);
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->drop('project_batch_eligible_students');
  }
};
