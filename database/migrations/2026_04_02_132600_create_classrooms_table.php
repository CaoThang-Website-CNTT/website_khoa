<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {

    // Majors
    $schema->create('majors', function (TableBuilder $table) {
      $table->id();

      $table->varchar('full_name', 100)
        ->nullable()
        ->comment('Tên ngành học đầy đủ');

      $table->varchar('short_name', 20)
        ->unique()
        ->comment('Tên viết tắt (VD: TH, CNTT)');

      $table->varchar('level', 5)
        ->nullable()
        ->comment('Hệ đào tạo (VD: CĐ, CĐN)');

      $table->bigInt('department_id')->unsigned();

      // Foreign Key Constraint
      $table->foreign('department_id')
        ->references('id')
        ->on('departments')
        ->onDelete('cascade');

      $table->timestamps();
      $table->softDeletes();
    });

    // Specializations
    $schema->create('specializations', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('major_id')->unsigned()
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

    // Classrooms
    $schema->create('classrooms', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('major_id')->unsigned();
      $table->bigInt('specialization_id')->unsigned()->nullable();
      $table->bigInt('homeroom_teacher_id')->unsigned()
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
