<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    $schema->create('media', function (TableBuilder $table) {
      $table->id();

      $table->varchar('title', 255);
      $table->varchar('file_name', 255);
      $table->varchar('file_path', 1000);
      $table->varchar('mime_type', 100);
      $table->varchar('alt_text', 255)->nullable();

      // Tối ưu truy vấn và tối ưu render cho image, video 
      // Các mime_type khác không đụng tới
      $table->int('width')->unsigned()->nullable()->default(null);
      $table->int('height')->unsigned()->nullable()->default(null);

      $table->bigInt('file_size')->unsigned();
      $table->json('metadata')->nullable()->default(null);

      $table->timestamps();

      // Index
      $table->index(['mime_type', 'created_at'], 'idx_media_mime_type_created_at');
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('media');

    $schema->enableForeignKeys();
  }
};
