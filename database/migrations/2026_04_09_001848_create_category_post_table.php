<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration
{
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    $schema->create('category_post', function ($table) {
      $table->bigInt('post_id')->unsigned();
      $table->bigInt('category_id')->unsigned();

      $table->foreign('post_id')
        ->references('id')
        ->on('posts')
        ->onDelete('cascade');

      $table->foreign('category_id')
        ->references('id')
        ->on('categories')
        ->onDelete('cascade');

      $table->primary(['post_id', 'category_id']);
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('category_post');

    $schema->enableForeignKeys();
  }
};
