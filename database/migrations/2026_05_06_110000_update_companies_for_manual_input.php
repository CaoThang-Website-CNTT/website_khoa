<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('companies', function (AlterBuilder $table) {
      $table->boolean('is_verified')->default(0)->after('note');
      $table->enum('source', ['api', 'manual'])->default('api')->after('is_verified');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('companies', function (AlterBuilder $table) {
      $table->dropColumn(['source', 'is_verified']);
    });
  }
};
