<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('project_groups', function (AlterBuilder $table) {
      $table->boolean('is_admin_approved_solo')->default(0)->after('status')->comment('Cờ cho phép 1 SV làm đồ án');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('project_groups', function (AlterBuilder $table) {
      $table->dropColumn('is_admin_approved_solo');
    });
  }
};
