<?php

use App\Migration\BaseMigration;
use App\Core\Schema\AlterBuilder;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('internship_weekly_reports', function (AlterBuilder $table) {
      $table->varchar('no_activity_reason', 50)->nullable()->after('is_exempt');
      $table->text('no_activity_note')->nullable()->after('no_activity_reason');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('internship_weekly_reports', function (AlterBuilder $table) {
      $table->dropColumn('no_activity_note');
      $table->dropColumn('no_activity_reason');
    });
  }
};
