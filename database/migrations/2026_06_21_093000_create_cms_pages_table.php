<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->create('cms_pages', function (TableBuilder $table) {
      $table->id();

      $table->varchar('title', 255);
      $table->varchar('slug', 255)->unique();
      $table->varchar('route_path', 255)->nullable()->comment('Đường dẫn công khai, ví dụ như / hoặc /gioi-thieu.');
      $table->varchar('type', 80)->default('landing_page')->comment('Danh mục trang.');
      $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->comment('Trạng thái xuất bản.');
      $table->enum('layout_mode', ['section_schema', 'block_builder'])->default('section_schema')->comment('Chế độ hiển thị; section_schema là phiên bản v1 và block_builder được để dành cho các trang kéo thả trong tương lai.');
      $table->json('content_json');
      $table->json('settings_json')->nullable();

      $table->timestamp('published_at')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->index('status');
      $table->index('route_path');
      $table->index(['type', 'layout_mode']);
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->drop('cms_pages');
  }
};