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
        $schema->alter('internship_weekly_reports', function (AlterBuilder $table) {
            $table->boolean('is_seen_by_teacher')->default(0)->comment('1 = GV đã xem');
            $table->text('teacher_feedback')->nullable()->comment('Nhận xét của GV');
            $table->timestamp('teacher_interacted_at')->nullable()->comment('Thời gian GV xem hoặc nhận xét');
        });
    }

    public function back(TableBuilder $schema): void
    {
        $schema->alter('internship_weekly_reports', function (AlterBuilder $table) {
            $table->dropColumn(['is_seen_by_teacher', 'teacher_feedback', 'teacher_interacted_at']);
        });
    }
};
