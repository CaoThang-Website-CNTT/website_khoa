<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    // Teachers
    $schema->create('teachers', function ($table) {
      $table->id();

      $table->bigInt('account_id')->unique()->nullable();
      $table->varchar('staff_code', 10)->unique();
      $table->varchar('full_name', 255);

      $table->enum('gender', ['male', 'female']);
      $table->date('dob')->nullable();
      $table->varchar('phone', 15)->nullable();
      $table->text('address')->nullable();
      $table->varchar('national_id', 12)->unique()->comment('CCCD của GV');

      $table->enum('degree', [
        'Cử nhân',
        'Thạc sĩ',
        'Tiến sĩ',
        'Phó giáo sư',
        'Giáo sư'
      ])->nullable()->comment('Học vị');

      $table->enum('title', [
        'Trợ giảng',
        'Giảng viên',
        'Giảng viên chính',
        'Nghiên cứu viên'
      ])->nullable()->comment('Chức danh khoa học');

      $table->enum('position', [
        'Giáo viên',
        'Trưởng bộ môn',
        'Phó bộ môn',
        'Trưởng khoa',
        'Phó khoa'
      ])->default('Giáo viên')->comment('Chức vụ hành chính');

      $table->bigInt('department_id')->nullable();

      $table->enum('contract_type', ['full_time', 'part_time', 'visiting', 'contract'])
        ->default('full_time');

      $table->date('start_date')->nullable();
      $table->date('end_date')->nullable();
      $table->text('notes')->nullable();

      $table->timestamps();
      $table->softDeletes();

      // Foreign Key Constraints
      $table->foreign('department_id')
        ->references('id')
        ->on('departments')
        ->onDelete('set null');
    });

    //Department
    $schema->create('departments', function ($table) {
      $table->id();

      $table->varchar('full_name', 150)->comment('Tên khoa (VD: Khoa Công nghệ thông tin)');
      $table->varchar('short_name', 20)->unique()->comment('Mã khoa (VD: CNTT)');
      $table->text('description')->nullable();

      $table->timestamps();
      $table->softDeletes();
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('teachers');
    $schema->drop('departments');

    $schema->enableForeignKeys();
  }
};
