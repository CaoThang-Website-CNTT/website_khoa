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

      $table->varchar('title', 255)->default("")->comment("Tiêu đề cho media (Optional)");
      $table->varchar('file_name', 255)->comment("Hệ thông tự tạo tên cho unique");
      $table->varchar('file_path', 1000)->comment("Đường dẫn tương đối");
      $table->varchar('mime_type', 100);
      $table->varchar('alt_text', 255)->nullable()->comment("Tối ưu SEO, aria");

      // Tối ưu truy vấn và tối ưu render cho image, video 
      // Các mime_type khác không đụng tới
      $table->int('width')->unsigned()->nullable()->default(null)->comment("Kích thước cho media dạng image/video, các media khác không cần đụng tới");
      $table->int('height')->unsigned()->nullable()->default(null)->comment("Kích thước cho media dạng image/video, các media khác không cần đụng tới");

      $table->bigInt('file_size')->unsigned();
      $table->json('metadata')->nullable()->default(null)->comment("
      JSON lưu thêm metadata chuyên dụng cho từng loại media");

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
