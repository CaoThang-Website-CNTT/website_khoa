<?php

use App\Migration\BaseMigration;
use App\Core\Schema\AlterBuilder;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('internship_batches', function (AlterBuilder $table) {
      $table->timestamp('grades_published_at')->nullable()->comment('Thời gian công bố điểm cho toàn đợt');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('internship_batches', function (AlterBuilder $table) {
      $table->dropColumn('grades_published_at');
    });
  }
};
