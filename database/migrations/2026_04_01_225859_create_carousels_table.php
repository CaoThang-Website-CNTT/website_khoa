<?php

use App\Migration\BaseMigration;
use App\Schema\TableBuilder;

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    // Carousels
    $schema->create('carousels', function (TableBuilder $table) {
      $table->id();

      $table->varchar('name', 100);
      $table->varchar('slug', 100)->unique();
      $table->tinyInt('is_active')->default(1);

      $table->timestamps();
      $table->softDeletes();
    });

    // Carousel Slides
    $schema->create('carousel_slides', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('carousel_id');

      $table->varchar('title', 255);
      $table->varchar('title_highlight', 255)->nullable();
      $table->text('description')->nullable();
      $table->varchar('image_path', 500);
      $table->varchar('image_alt', 255)->default('');

      $table->varchar('cta_label', 100)->nullable();
      $table->varchar('cta_url', 500)->nullable();
      $table->varchar('cta_variant', 20)->default('primary');

      $table->mediumText('custom_html')->nullable();
      $table->tinyInt('use_custom_html')->default(0);

      $table->smallInt('sort_order')->default(0);
      $table->tinyInt('is_active')->default(1);

      $table->timestamps();
      $table->softDeletes();

      // Foreign Key Constraint
      $table->foreign('carousel_id')
        ->references('id')
        ->on('carousels')
        ->onDelete('cascade');
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('carousels');
    $schema->drop('carousel_slides');

    $schema->enableForeignKeys();
  }
};
