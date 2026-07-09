<?php
use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;
use App\Core\Schema\AlterBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void {
    $schema->alter('referral_letters', function (AlterBuilder $table) {
      $table->varchar('approver_name', 255)->nullable()->after('document_number');
    });
    $schema->alter('internship_batch_students', function (AlterBuilder $table) {
      $table->varchar('company_mentor_name', 255)->nullable()->after('position');
      $table->varchar('company_mentor_phone', 20)->nullable()->after('company_mentor_name');
      $table->varchar('company_mentor_email', 255)->nullable()->after('company_mentor_phone');
    });
  }
  public function back(TableBuilder $schema): void {
    $schema->alter('referral_letters', fn(AlterBuilder $table) => $table->dropColumn('approver_name'));
    $schema->alter('internship_batch_students', fn(AlterBuilder $table) => $table->dropColumn(['company_mentor_name', 'company_mentor_phone', 'company_mentor_email']));
  }
};
