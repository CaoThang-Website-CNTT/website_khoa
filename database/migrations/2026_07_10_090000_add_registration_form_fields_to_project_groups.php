<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration
{
    public function forward(TableBuilder $schema): void
    {
        $schema->alter('project_groups', function (AlterBuilder $table) {
            $table->text('registration_requirements')->nullable()->after('assigned_at');
            $table->text('supervisor_opinion')->nullable()->after('registration_requirements');
            $table->date('execution_start')->nullable()->after('supervisor_opinion');
            $table->date('execution_end')->nullable()->after('execution_start');
        });
    }

    public function back(TableBuilder $schema): void
    {
        $schema->alter('project_groups', function (AlterBuilder $table) {
            $table->dropColumn('execution_end');
            $table->dropColumn('execution_start');
            $table->dropColumn('supervisor_opinion');
            $table->dropColumn('registration_requirements');
        });
    }
};
