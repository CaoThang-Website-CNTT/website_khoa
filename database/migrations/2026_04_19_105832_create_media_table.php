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
      $table->bigInt('post_id')->unsigned()->unique();

      $table->text("disk_path");
      $table->text("mime_type");
      $table->bigInt('size_bytes')->unsigned();

      $table->text("original_name");
      $table->text("alt_text");

      $table->enum('status', ['pending', 'attached', 'archived']);

      $table->timestamps();

      $table->foreign('post_id')
        ->references('id')
        ->on('posts')
        ->onDelete('cascade');
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('');

    $schema->enableForeignKeys();
  }
};
