<?php

use App\Migration\BaseMigration;
use App\Schema\TableBuilder;

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    $schema->create('students', function ($table) {
      $table->id();

      $table->bigInt('account_id')->unique();

      $table->varchar('full_name', 255);
      $table->enum('gender', ['male', 'female']);
      $table->date('dob');
      $table->varchar('phone', 15);
      $table->text('address')->nullable();
      $table->varchar('national_id', 12)
        ->unique()
        ->comment('Số CCCD của sinh viên');
      $table->varchar('birth_place', 255);

      $table->varchar('student_id', 10)
        ->unique()
        ->comment('Mã số sinh viên (Duy nhất)');
      $table->bigInt('classroom_id');
      $table->varchar('major', 150)
        ->comment('Chuyên ngành đào tạo (Lưu text)');
      $table->enum('status', ['Đang học', 'Đã tốt nghiệp', 'Tạm ngưng', 'Thôi học'])
        ->default('Đang học');
      $table->text('notes')->nullable();

      $table->timestamps();
      $table->softDeletes();

      // FK
      $table->foreign('account_id')
        ->on('accounts')
        ->references('id')
        ->onDelete('cascade');

      $table->foreign('classroom_id')
        ->on('classrooms')
        ->references('id')
        ->onDelete('set null');
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('students');

    $schema->enableForeignKeys();
  }
};
