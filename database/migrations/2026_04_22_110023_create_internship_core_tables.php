<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->create('internship_batches', function (TableBuilder $table) {
      $table->id();

      $table->varchar('title', 255);
      $table->text('description')->nullable();
      $table->int('class_of')->comment('Nien khoa, VD: 23');
      $table->enum('level', ['CĐ', 'CĐN'])->comment('Bac hoc');
      $table->dateTime('start_at');
      $table->dateTime('end_at');
      $table->enum('status', ['draft', 'public', 'closed'])->default('draft');

      $table->bigInt('created_by')->unsigned()->nullable();
      $table->timestamp('published_at')->nullable();
      $table->timestamp('closed_at')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->foreign('created_by')
        ->references('id')
        ->on('accounts')
        ->onDelete('set null');

      $table->index(['status', 'start_at', 'end_at'], 'idx_batches_status_time');
      $table->index(['status', 'published_at'], 'idx_batches_publish_state');
    });

    $schema->create('internship_batch_majors', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('batch_id')->unsigned();
      $table->bigInt('major_id')->unsigned();
      $table->timestamps();

      $table->foreign('batch_id')
        ->references('id')
        ->on('internship_batches')
        ->onDelete('cascade');

      $table->foreign('major_id')
        ->references('id')
        ->on('majors')
        ->onDelete('cascade');

      $table->unique(['batch_id', 'major_id'], 'uq_batch_major');
    });

    $schema->create('internship_batch_classrooms', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('batch_id')->unsigned();
      $table->bigInt('classroom_id')->unsigned();
      $table->timestamps();

      $table->foreign('batch_id')
        ->references('id')
        ->on('internship_batches')
        ->onDelete('cascade');

      $table->foreign('classroom_id')
        ->references('id')
        ->on('classrooms')
        ->onDelete('cascade');

      $table->unique(['batch_id', 'classroom_id'], 'uq_batch_classroom');
    });

    $schema->create('internship_batch_students', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('batch_id')->unsigned();
      $table->bigInt('student_id')->unsigned();
      $table->enum('status', ['pending', 'in_progress', 'passed', 'failed', 'cancelled'])->default('pending');
      $table->enum('source', ['db_select', 'excel_import'])->default('db_select');
      $table->text('note')->nullable();
      $table->timestamps();

      $table->foreign('batch_id')
        ->references('id')
        ->on('internship_batches')
        ->onDelete('cascade');

      $table->foreign('student_id')
        ->references('id')
        ->on('students')
        ->onDelete('cascade');

      $table->unique(['batch_id', 'student_id'], 'uq_batch_student');
      $table->index(['batch_id', 'status'], 'idx_batch_students_batch_status');
    });

    $schema->create('internship_batch_supervisors', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('batch_id')->unsigned();
      $table->bigInt('teacher_id')->unsigned();
      $table->int('max_students')->nullable();
      $table->boolean('is_active')->default(1);
      $table->timestamps();

      $table->foreign('batch_id')
        ->references('id')
        ->on('internship_batches')
        ->onDelete('cascade');

      $table->foreign('teacher_id')
        ->references('id')
        ->on('teachers')
        ->onDelete('cascade');

      $table->unique(['batch_id', 'teacher_id'], 'uq_batch_teacher');
      $table->index(['batch_id', 'is_active'], 'idx_batch_supervisors_active');
    });

    $schema->create('internship_assignments', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('batch_student_id')->unsigned();
      $table->bigInt('teacher_id')->unsigned();
      $table->enum('status', ['draft', 'published'])->default('draft');
      $table->enum('assignment_method', ['manual', 'auto_even', 'auto_shuffle'])->default('manual');
      $table->timestamp('assigned_at')->default('CURRENT_TIMESTAMP');
      $table->bigInt('assigned_by')->unsigned()->nullable();
      $table->text('note')->nullable();
      $table->timestamps();

      $table->foreign('batch_student_id')
        ->references('id')
        ->on('internship_batch_students')
        ->onDelete('cascade');

      $table->foreign('teacher_id')
        ->references('id')
        ->on('teachers')
        ->onDelete('cascade');

      $table->foreign('assigned_by')
        ->references('id')
        ->on('accounts')
        ->onDelete('set null');

      $table->index(['teacher_id', 'status'], 'idx_assign_teacher_status');
      $table->index(['batch_student_id', 'status'], 'idx_assign_student_status');
      $table->unique('batch_student_id', 'uq_assign_student');
    });

    $schema->create('assignment_logs', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('assignment_id')->unsigned();
      $table->enum('action', ['CREATE', 'UPDATE', 'DELETE', 'PUBLISH']);
      $table->bigInt('old_teacher_id')->unsigned()->nullable();
      $table->bigInt('new_teacher_id')->unsigned()->nullable();
      $table->bigInt('performed_by')->unsigned()->nullable();
      $table->varchar('reason', 255)->nullable();
      $table->timestamp('created_at')->default('CURRENT_TIMESTAMP');

      $table->foreign('assignment_id')
        ->references('id')
        ->on('internship_assignments')
        ->onDelete('cascade');

      $table->foreign('old_teacher_id')
        ->references('id')
        ->on('teachers')
        ->onDelete('set null');

      $table->foreign('new_teacher_id')
        ->references('id')
        ->on('teachers')
        ->onDelete('set null');

      $table->foreign('performed_by')
        ->references('id')
        ->on('accounts')
        ->onDelete('set null');
        
      $table->index('assignment_id', 'idx_logs_assignment');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('assignment_logs');
    $schema->drop('internship_assignments');
    $schema->drop('internship_batch_supervisors');
    $schema->drop('internship_batch_students');
    $schema->drop('internship_batch_classrooms');
    $schema->drop('internship_batch_majors');
    $schema->drop('internship_batches');

    $schema->enableForeignKeys();
  }
};
