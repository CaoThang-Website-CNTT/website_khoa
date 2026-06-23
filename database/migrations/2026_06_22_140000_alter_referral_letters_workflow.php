<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;
use App\Core\Schema\AlterBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    // 1. Thêm các cột mới vào bảng referral_letters
    $schema->alter('referral_letters', function (AlterBuilder $table) {
      $table->bigInt('teacher_id')->unsigned()->nullable()->after('company_id');
      $table->date('internship_start_date')->nullable();
      $table->date('internship_end_date')->nullable();
      $table->varchar('document_number', 50)->nullable()->comment('Số công văn');
      $table->text('note')->nullable();

      $table->modifyColumn(fn($t) => $t->bigInt('batch_student_id')->unsigned()->nullable()->comment('Người đăng ký đại diện'));

      $table->addForeign('teacher_id')->references('id')->on('teachers')->onDelete('set null');
    });

    // 2. Tạo bảng referral_letter_students
    $schema->create('referral_letter_students', function (TableBuilder $table) {
      $table->id();
      $table->bigInt('referral_letter_id')->unsigned();
      $table->varchar('full_name', 255);
      $table->varchar('training_program', 255)->nullable()->comment('Ngành/nghề đào tạo');
      $table->date('dob')->nullable();
      $table->text('address')->nullable();

      $table->bigInt('student_id')->unsigned()->nullable();
      $table->bigInt('batch_student_id')->unsigned()->nullable();
      $table->int('sort_order')->default(0);

      $table->timestamps();

      $table->foreign('referral_letter_id')
        ->references('id')
        ->on('referral_letters')
        ->onDelete('cascade');

      $table->foreign('student_id')
        ->references('id')
        ->on('students')
        ->onDelete('set null');

      $table->foreign('batch_student_id')
        ->references('id')
        ->on('internship_batch_students')
        ->onDelete('set null');

      $table->index('referral_letter_id', 'idx_rls_letter_id');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('referral_letter_students');

    $schema->alter('referral_letters', function (AlterBuilder $table) {
      try {
        $table->dropForeign('teacher_id'); // sẽ xoá theo tên tự sinh fk_referral_letters_teacher_id
      } catch (\Exception $e) {
      }

      $table->dropColumn(['teacher_id', 'internship_start_date', 'internship_end_date', 'document_number', 'note']);
      $table->modifyColumn(fn($t) => $t->bigInt('batch_student_id')->unsigned());
    });

    $schema->enableForeignKeys();
  }
};
