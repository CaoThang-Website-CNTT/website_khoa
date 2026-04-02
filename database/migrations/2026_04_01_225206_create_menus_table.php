<?php

use App\Migration\BaseMigration;
use App\Schema\TableBuilder;

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    // Menus Table
    $schema->create('menus', function (TableBuilder $table) {
      $table->id();

      $table->varchar('key', 60)->unique();
      $table->varchar('label', 100);
      $table->varchar('description', 255)->nullable();

      $table->enum('type', ['const', 'custom'])
        ->default('const');

      $table->tinyInt('sort_order')
        ->default(0);

      $table->timestamps();
      $table->softDeletes();
    });

    // Menu Items Table
    $schema->create('menu_items', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('menu_id');
      $table->bigInt('parent_id')->nullable();

      $table->varchar('label', 150);
      $table->varchar('url', 500);

      $table->tinyInt('sort_order')
        ->default(0);

      $table->timestamps();
      $table->softDeletes();

      // Foreign Key
      $table->foreign('menu_id')
        ->references('id')
        ->on('menus')
        ->onDelete('cascade');

      $table->foreign('parent_id')
        ->references('id')
        ->on('menu_items')
        ->onDelete('cascade');
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('menus');
    $schema->date('menu_items');

    $schema->enableForeignKeys();
  }
};
