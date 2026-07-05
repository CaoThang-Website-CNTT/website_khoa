<?php

use App\Migration\BaseMigration;
use App\Core\Schema\TableBuilder;
use App\Core\Schema\AlterBuilder;

return new class extends BaseMigration {
  public function forward(TableBuilder $schema): void
  {
    // Chuyển dữ liệu cũ trước khi loại bỏ giá trị "printed" khỏi enum.
    $this->updateData('referral_letters', ['status' => 'approved'], ['status' => 'printed']);

    $schema->alter('referral_letters', function (AlterBuilder $table) {
      $table->modifyColumn(fn($t) => $t
        ->enum('status', ['pending', 'approved', 'completed', 'received', 'rejected', 'cancelled'])
        ->default('pending'));
      $table->varchar('recipient_name', 255)->nullable()->after('cancelled_by');
      $table->varchar('recipient_phone', 15)->nullable()->after('recipient_name');
      $table->varchar('recipient_email', 255)->nullable()->after('recipient_phone');
      $table->timestamp('received_at')->nullable()->after('recipient_email');
      $table->bigInt('received_by')->unsigned()->nullable()->after('received_at');
      $table->addForeign('received_by')->references('id')->on('accounts')->onDelete('set null');
    });
  }

  public function back(TableBuilder $schema): void
  {
    $this->updateData('referral_letters', ['status' => 'approved'], ['status' => 'completed']);
    $this->updateData('referral_letters', ['status' => 'approved'], ['status' => 'received']);
    $schema->alter('referral_letters', function (AlterBuilder $table) {
      $table->dropForeign('received_by');
      $table->dropColumn(['recipient_name', 'recipient_phone', 'recipient_email', 'received_at', 'received_by']);
      $table->modifyColumn(fn($t) => $t
        ->enum('status', ['pending', 'approved', 'printed', 'rejected', 'cancelled'])
        ->default('pending'));
    });
  }
};
