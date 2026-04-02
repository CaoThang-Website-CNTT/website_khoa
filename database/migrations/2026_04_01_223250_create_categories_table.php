<?php

use App\Migration\BaseMigration;
use App\Schema\TableBuilder;

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    $schema->create('categories', function ($table) {
      $table->id();

      $table->varchar('name', 255);

      $table->varchar('slug', 255)
        ->unique()
        ->nullable();

      $table->enum('type', ['const', 'custom'])
        ->default('custom');

      $table->longText('description')
        ->nullable();

      $table->bigInt('parent_id')
        ->nullable()
        ->comment('ID của danh mục cha (Self-referencing)');

      $table->json('meta')
        ->nullable();

      $table->timestamps();
      $table->softDeletes();

      // Foreign Key
      $table->foreign('parent_id')
        ->references('id')
        ->on('categories')
        ->onDelete('set null');
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('categories');

    $schema->enableForeignKeys();
  }
};
