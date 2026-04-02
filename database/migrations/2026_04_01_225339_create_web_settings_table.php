<?php

use App\Migration\BaseMigration;
use App\Schema\TableBuilder;

return new class extends BaseMigration {
  /**
   * Chạy migration để tạo bảng
   */
  public function forward(TableBuilder $schema): void
  {
    $schema->create('web_settings', function (TableBuilder $table) {
      $table->id();

      $table->varchar('key', 120)->unique();

      $table->varchar('group', 60)->default('general');
      $table->varchar('group_label', 60)->default('General');

      $table->enum('type', [
        'string',
        'text',
        'email',
        'url',
        'json',
        'bool',
        'int',
        'float',
        'datetime'
      ])->default('string');

      $table->mediumText('value')->nullable();
      $table->text('default_value')->nullable();

      $table->varchar('label', 150);
      $table->varchar('description', 255)->nullable();

      $table->tinyInt('autoload')->default(1);
      $table->tinyInt('is_locked')->default(0);

      $table->smallInt('sort_order')->default(0);
      $table->bigInt('updated_by')->nullable();

      $table->timestamps();
    });
  }
  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('web_settings');

    $schema->enableForeignKeys();
  }
};
