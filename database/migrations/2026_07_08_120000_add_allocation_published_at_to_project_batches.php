<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('project_batches', function (AlterBuilder $table) {
      $table->timestamp('allocation_published_at')->nullable()->after('closed_at')->comment('Thời điểm Admin chốt và công bố kết quả phân bổ cho SV/GV');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('project_batches', function (AlterBuilder $table) {
      $table->dropColumn('allocation_published_at');
    });
  }
};
