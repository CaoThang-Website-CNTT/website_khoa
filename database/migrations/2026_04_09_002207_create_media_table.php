<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    $schema->create('medias', function ($table) {
      $table->id();

      $table->varchar('file_name', 255);
      $table->varchar('file_path', 255);
      $table->varchar('mime_type', 100);
      $table->int('file_size');
      $table->varchar('alt_text', 255)->nullable();

      $table->bigInt('post_id')->unsigned()->nullable();
      $table->timestamps();

      // Foreign key
      $table->foreign('post_id')
        ->references('id')
        ->on('posts')
        ->onDelete('cascade');
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('medias');

    $schema->enableForeignKeys();
  }
};
