<?php

use App\Migration\BaseMigration;
use App\Core\Schema\{TableBuilder, AlterBuilder};

return new class extends BaseMigration
{
    /**
     * Chạy migration để tạo bảng
     */
    public function forward(TableBuilder $schema): void
    {
        $schema->create('project_batches', function (TableBuilder $table) {
            $table->id();
            $table->varchar('title', 255);
            $table->text('description')->nullable();
            $table->dateTime('topic_proposal_start')->nullable();
            $table->dateTime('topic_proposal_end')->nullable();
            $table->dateTime('registration_start')->nullable();
            $table->dateTime('registration_end')->nullable();
            $table->int('max_aspirations')->default(3);
            $table->int('class_of')->default(0);
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->bigInt('created_by')->unsigned()->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('created_by')
              ->references('id')
              ->on('accounts')
              ->onDelete('set null');
        });

        $schema->create('project_batch_supervisors', function (TableBuilder $table) {
            $table->id();
            
            $table->bigInt('batch_id')->unsigned();
            $table->bigInt('teacher_id')->unsigned();
            $table->int('min_students')->default(0);
            $table->int('max_students')->default(20);
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('project_batches')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');

            $table->unique(['batch_id', 'teacher_id'], 'uq_project_batch_teacher');
            $table->index(['batch_id', 'is_active'], 'idx_project_supervisors_active');
        });

        $schema->create('project_topics', function (TableBuilder $table) {
            $table->id();
            $table->bigInt('batch_id')->unsigned();
            $table->bigInt('teacher_id')->unsigned();
            $table->varchar('title', 500);
            $table->text('description')->nullable();
            $table->varchar('pdf_file_path', 500)->nullable();
            $table->int('max_students')->default(2);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->text('reject_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->bigInt('reviewed_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('batch_id')->references('id')->on('project_batches')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('accounts')->onDelete('set null');
        });

        $schema->create('project_groups', function (TableBuilder $table) {
            $table->id();
            $table->bigInt('batch_id')->unsigned();
            $table->bigInt('leader_student_id')->unsigned();
            $table->bigInt('assigned_topic_id')->unsigned()->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            
            $table->foreign('batch_id')->references('id')->on('project_batches')->onDelete('cascade');
            $table->foreign('leader_student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('assigned_topic_id')->references('id')->on('project_topics')->onDelete('set null');
        });

        $schema->create('project_group_members', function (TableBuilder $table) {
            $table->id();
            $table->bigInt('group_id')->unsigned();
            $table->bigInt('student_id')->unsigned();
            $table->boolean('is_leader')->default(0);
            $table->boolean('is_confirmed')->default(0);
            $table->boolean('is_eligible')->default(1);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('group_id')->references('id')->on('project_groups')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            $table->unique(['group_id', 'student_id'], 'uq_project_group_member');
        });

        $schema->create('project_aspirations', function (TableBuilder $table) {
            $table->id();
            $table->bigInt('group_id')->unsigned();
            $table->bigInt('topic_id')->unsigned();
            $table->int('priority');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            
            $table->foreign('group_id')->references('id')->on('project_groups')->onDelete('cascade');
            $table->foreign('topic_id')->references('id')->on('project_topics')->onDelete('cascade');
            
            $table->unique(['group_id', 'topic_id'], 'uq_project_aspiration');
            $table->index(['topic_id', 'priority', 'created_at'], 'idx_project_aspiration_topic');
        });
    }

    public function back(TableBuilder $schema): void
    {
        $schema->disableForeignKeys();

        $schema->drop('project_aspirations');
        $schema->drop('project_group_members');
        $schema->drop('project_groups');
        $schema->drop('project_topics');
        $schema->drop('project_batch_supervisors');
        $schema->drop('project_batches');

        $schema->enableForeignKeys();
    }
};
