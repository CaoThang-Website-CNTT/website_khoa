<?php

use App\Migration\BaseMigration;
use App\Schema\TableBuilder;

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    $schema->create('accounts', function (TableBuilder $table) {
      $table->id()->comment('ID định danh duy nhất của tài khoản');

      $table->varchar('email', 255)
        ->unique()
        ->comment('Địa chỉ email dùng để đăng nhập');

      $table->varchar('password_hash', 500)
        ->comment("Mật khẩu đã mã hóa theo chuẩn 2y hash");

      $table->enum('role', ['student', 'teacher', 'admin'])
        ->comment('Vai trò của người dùng trong hệ thống');

      $table->timestamps();

      $table->softDeletes();
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('accounts');

    $schema->enableForeignKeys();
  }
};
