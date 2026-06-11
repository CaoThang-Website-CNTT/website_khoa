<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;
use App\Core\Schema\AlterBuilder;

return new class extends BaseMigration
{
  public function forward(TableBuilder $schema): void
  {
    $schema->alter('internship_submissions', function (AlterBuilder $table) {
      // Sửa enum 'type' để hỗ trợ các loại tài liệu riêng biệt
      $table->modifyColumn(function ($t) {
        $t->enum('type', [
          'internship_report',
          'evaluation_form',
          'company_survey',
          'related_photo'
        ]);
      });

      $table->varchar('mime_type', 100)->nullable()->after('original_file_name');

      // is_latest scope theo từng type
      $table->addIndex(['batch_student_id', 'type', 'is_latest'], 'idx_submissions_latest');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $schema->alter('internship_submissions', function (AlterBuilder $table) {
      $table->dropIndex('idx_submissions_latest');
      $table->addIndex(['batch_student_id', 'is_latest'], 'idx_submissions_latest');

      $table->dropColumn('mime_type');

      $table->modifyColumn(function ($t) {
        $t->enum('type', [
          'internship_form',
          'internship_report'
        ])->nullable();
      });
    });
  }
};
