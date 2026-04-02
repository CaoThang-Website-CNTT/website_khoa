<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    // Classrooms
    $schema->create('classrooms', function ($table) {
      $table->id();

      $table->bigInt('major_id');
      $table->bigInt('specialization_id')->nullable();
      $table->bigInt('homeroom_teacher_id')
        ->nullable()
        ->comment('ID của Giáo viên chủ nhiệm');
      $table->int('class_of')
        ->comment('Khóa học (Ví dụ: 23 cho khóa 2023)');
      $table->varchar('letter', 1)->nullable();
      $table->varchar('short_name', 50)
        ->unique()
        ->comment('Mã lớp (Ví dụ: CĐ TH 23A)');

      $table->timestamps();
      $table->softDeletes();

      // Foreign Key Constraint
      $table->foreign('major_id')
        ->references('id')
        ->on('majors')
        ->onDelete('cascade');

      $table->foreign('specialization_id')
        ->references('id')
        ->on('specializations')
        ->onDelete('set null');
    });

    // Majors
    $schema->create('majors', function ($table) {
      $table->id()->comment('Khóa chính');

      $table->varchar('full_name', 100)
        ->nullable()
        ->comment('Tên ngành học đầy đủ');

      $table->varchar('short_name', 20)
        ->unique()
        ->comment('Tên viết tắt (VD: TH, CNTT)');

      $table->varchar('level', 5)
        ->nullable()
        ->comment('Hệ đào tạo (VD: CĐ, CĐN)');

      $table->bigInt('department_id');

      // Foreign Key Constraint
      $table->foreign('department_id')
        ->references('id')
        ->on('department')
        ->onDelete('cascade');

      $table->timestamps();
      $table->softDeletes();
    });

    // Specializations
    $schema->create('specializations', function ($table) {
      $table->id();

      $table->bigInt('major_id')
        ->comment('FK trỏ tới majors');

      $table->varchar('full_name', 100)->nullable();
      $table->varchar('short_name', 20)->nullable();

      $table->timestamps();
      $table->softDeletes();

      // Foreign Key Constraint
      $table->foreign('major_id')
        ->references('id')
        ->on('majors')
        ->onDelete('cascade');

      $table->unique(['major_id', 'short_name'], 'unique_spec_per_major');
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('classrooms');
    $schema->drop('majors');
    $schema->drop('specializations');

    $schema->enableForeignKeys();
  }
};
