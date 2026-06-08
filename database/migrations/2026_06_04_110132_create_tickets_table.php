<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    $schema->create('tickets', function (TableBuilder $table) {
      $table->id();

      $table->varchar('title', 255);
      $table->text('description');
      $table->enum('type', ['bug', 'improvement', 'feedback']);
      $table->enum('status', ['pending', 'processing', 'resolved', 'rejected'])->default("pending");
      $table->varchar('reporter_email', 100);

      $table->timestamps();
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('tickets');

    $schema->enableForeignKeys();
  }
};
