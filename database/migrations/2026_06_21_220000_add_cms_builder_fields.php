<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('cms_pages', function ($table) {
      $table->json('builder_draft_json')
        ->nullable()
        ->after('settings_json')
        ->comment('Builder data nháp.');

      $table->json('builder_published_json')
        ->nullable()
        ->after('builder_draft_json')
        ->comment('Builder data đã approve.');

      $table->json('builder_snapshots_json')
        ->nullable()
        ->after('builder_published_json')
        ->comment('Cho undo/redo.');

      $table->timestamp('builder_enabled_at')
        ->nullable()
        ->after('builder_snapshots_json')
        ->comment('Thời điểm bật CMS v2.');

      $table->addIndex('builder_enabled_at', 'idx_cms_pages_builder_enabled_at');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('cms_pages', function ($table) {
      $table->dropIndex('idx_cms_pages_builder_enabled_at');
      $table->dropColumn([
        'builder_draft_json',
        'builder_published_json',
        'builder_snapshots_json',
        'builder_enabled_at',
      ]);
    });
  }
};
