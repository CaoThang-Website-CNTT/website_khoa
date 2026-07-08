<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('project_aspirations', function (AlterBuilder $table) {
      $table->dateTime('locked_at')->nullable()->after('status');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('project_aspirations', function (AlterBuilder $table) {
      $table->dropColumn('locked_at');
    });
  }
};
