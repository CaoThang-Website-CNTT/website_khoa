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
        $schema->create('email_jobs', function (TableBuilder $table) {
          $table->id();

          $table->varchar('to_email', 255);
          $table->varchar('to_name', 255)->nullable();
          $table->varchar('subject', 255);
          $table->text('body');
          $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
          $table->text('error_message')->nullable();
          $table->int('attempts')->default(0);

          $table->timestamps();
        });        
    }
    public function back(TableBuilder $schema): void
    {
        $schema->disableForeignKeys();

        $schema->drop('email_jobs');

        $schema->enableForeignKeys();
    }
};
