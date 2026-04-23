<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->create('companies', function (TableBuilder $table) {
      $table->id();

      $table->varchar('tax_code', 50)->nullable();
      $table->varchar('name', 255);
      $table->varchar('normalized_name', 255)->nullable();
      $table->varchar('phone', 15)->nullable();
      $table->varchar('email', 255)->nullable();
      $table->varchar('website', 255)->nullable();
      $table->text('address')->nullable();
      $table->text('note')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->unique('tax_code', 'uq_companies_tax_code');
      $table->index(['normalized_name', 'phone'], 'idx_companies_name_phone');
    });

    $schema->create('internship_grades', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('batch_student_id')->unsigned();
      $table->decimal('final_score', 4, 2)->nullable();
      $table->varchar('score_reason', 255)->nullable()->comment('Bat buoc khi final_score = null');
      $table->text('feedback')->nullable();
      $table->timestamp('graded_at')->nullable();
      $table->bigInt('graded_by')->unsigned()->nullable();
      $table->timestamp('grade_lock_at')->nullable()->comment('Khoa nop lai khi GV chot diem');
      $table->timestamps();

      $table->foreign('batch_student_id')
        ->references('id')
        ->on('internship_batch_students')
        ->onDelete('cascade');

      $table->foreign('graded_by')
        ->references('id')
        ->on('teachers')
        ->onDelete('set null');

      $table->unique('batch_student_id', 'uq_grade_batch_student');
      $table->index('grade_lock_at', 'idx_grade_lock_at');
    });

    $schema->create('internship_submissions', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('batch_student_id')->unsigned();
      $table->enum('type', ['internship_form', 'internship_report']);
      $table->enum('storage_mode', ['file', 'link'])->default('file');
      $table->varchar('file_path', 255)->nullable();
      $table->varchar('external_url', 500)->nullable();
      $table->timestamp('submitted_at')->default('CURRENT_TIMESTAMP');
      $table->boolean('is_latest')->default(1);
      $table->timestamps();

      $table->foreign('batch_student_id')
        ->references('id')
        ->on('internship_batch_students')
        ->onDelete('cascade');

      $table->index(['batch_student_id', 'type', 'is_latest'], 'idx_submissions_latest');
      $table->index(['batch_student_id', 'submitted_at'], 'idx_submissions_timeline');
    });

    $schema->create('referral_letters', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('batch_student_id')->unsigned();
      $table->bigInt('company_id')->unsigned();
      $table->enum('status', ['pending', 'printed', 'cancelled'])->default('pending');
      $table->text('cancel_reason')->nullable();
      $table->timestamp('printed_at')->nullable();
      $table->bigInt('processed_by')->unsigned()->nullable();
      $table->timestamps();

      $table->foreign('batch_student_id')
        ->references('id')
        ->on('internship_batch_students')
        ->onDelete('cascade');

      $table->foreign('company_id')
        ->references('id')
        ->on('companies')
        ->onDelete('restrict');

      $table->foreign('processed_by')
        ->references('id')
        ->on('accounts')
        ->onDelete('set null');

      $table->index(['batch_student_id', 'status'], 'idx_referral_status');
    });

    $schema->create('batch_student_exceptions', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('batch_student_id')->unsigned();
      $table->dateTime('allow_late_submission_until');
      $table->varchar('reason', 255)->nullable();
      $table->bigInt('approved_by')->unsigned()->nullable();
      $table->timestamps();

      $table->foreign('batch_student_id')
        ->references('id')
        ->on('internship_batch_students')
        ->onDelete('cascade');

      $table->foreign('approved_by')
        ->references('id')
        ->on('accounts')
        ->onDelete('set null');

      $table->index(['batch_student_id', 'allow_late_submission_until'], 'idx_student_exceptions');
    });

  }

  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('batch_student_exceptions');
    $schema->drop('referral_letters');
    $schema->drop('internship_submissions');
    $schema->drop('internship_grades');
    $schema->drop('companies');

    $schema->enableForeignKeys();
  }
};
