<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration
{
    /**
     * Chạy migration
     */
    public function forward(TableBuilder $schema): void
    {
        $schema->alter('project_batches', function (AlterBuilder $table) {
            // Rename class_of to min_class_of
            $table->renameColumn('class_of', 'min_class_of', fn($t) => $t->int('min_class_of')->default(0)->comment('Nien khoa, VD: 23'));
            
            // Add max_class_of
            $table->int('max_class_of')->default(0)->after('min_class_of')->comment('Nien khoa max, VD: 25');
        });
    }

    /**
     * Rollback migration
     */
    public function back(TableBuilder $schema): void
    {
        $schema->alter('project_batches', function (AlterBuilder $table) {
            $table->dropColumn('max_class_of');
            $table->renameColumn('min_class_of', 'class_of', fn($t) => $t->int('class_of')->default(0));
        });
    }
};
