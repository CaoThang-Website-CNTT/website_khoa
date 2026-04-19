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
    $schema->create('posts', function ($table) {
      $table->id();

      $table->varchar('title', 255);
      $table->varchar('slug', 255)->unique();
      $table->json('content_json');
      $table->bigInt('author_id')->unsigned();
      $table->enum('status', ['draft', 'published', 'deleted'])->default('draft');
      $table->int('view_count')->default(0);
      $table->varchar('seo_description', 400)->nullable();
      $table->varchar('seo_image_url', 255)->nullable();

      $table->timestamp('published_at')->nullable();
      $table->timestamps();
      $table->softDeletes();

      // FK
      $table->foreign('author_id')
        ->references('id')
        ->on('accounts')
        ->onDelete('cascade');
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('posts');

    $schema->enableForeignKeys();
  }
};