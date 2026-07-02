<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    $schema->create('internship_weekly_reports', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('batch_student_id')->unsigned();
      $table->int('week_number')->comment('Số thứ tự tuần (1, 2, 3, ...)');
      $table->date('week_start')->comment('Ngày Thứ 2 đầu tuần');
      $table->date('week_end')->comment('Ngày CN cuối tuần');
      $table->text('content')->nullable()->comment('Nội dung công việc đã làm trong tuần. NULL nếu is_exempt = 1');
      $table->boolean('is_exempt')->default(0)->comment('1 = Ngoại lệ (chưa bắt đầu/nghỉ phép/kết thúc sớm)');
      $table->boolean('is_late')->default(0)->comment('1 = nộp muộn (sau tuần đó)');
      $table->boolean('is_latest')->default(1)->comment('1 = bản nộp mới nhất');
      $table->timestamp('submitted_at')->default('CURRENT_TIMESTAMP');

      $table->timestamps();

      $table->foreign('batch_student_id')
        ->references('id')
        ->on('internship_batch_students')
        ->onDelete('cascade');

      $table->index(['batch_student_id', 'week_number'], 'idx_batch_student_week');
      $table->index(['batch_student_id', 'is_latest'], 'idx_batch_student_latest');
    });

    $schema->create('internship_weekly_report_images', function (TableBuilder $table) {
      $table->id();

      $table->bigInt('weekly_report_id')->unsigned();
      $table->varchar('original_file_name', 255);
      $table->varchar('mime_type', 100)->nullable();
      $table->varchar('file_path', 500)->comment('Đường dẫn file trên server');
      $table->int('file_size')->nullable()->comment('Dung lượng file (bytes)');
      $table->timestamp('created_at')->default('CURRENT_TIMESTAMP');

      $table->foreign('weekly_report_id')
        ->references('id')
        ->on('internship_weekly_reports')
        ->onDelete('cascade');

      $table->index('weekly_report_id', 'idx_report_id');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->disableForeignKeys();

    $schema->drop('internship_weekly_report_images');
    $schema->drop('internship_weekly_reports');

    $schema->enableForeignKeys();
  }
};
