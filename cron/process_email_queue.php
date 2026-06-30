<?php

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/includes/env_loader.php';
// Load environment variables
if (file_exists(BASE_PATH . '/.env.staging')) {
  App\EnvLoader::load(BASE_PATH . '/.env.staging');
} elseif (file_exists(BASE_PATH . '/.env.local')) {
  App\EnvLoader::load(BASE_PATH . '/.env.local');
} elseif (file_exists(BASE_PATH . '/.env')) {
  App\EnvLoader::load(BASE_PATH . '/.env');
}

// Bật chế độ ghi log lỗi
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/tests/cron_email.log');

require_once BASE_PATH . '/startup.php';

use App\Stores\EmailJobStore;
use App\Services\MailService;

echo "[" . date('Y-m-d H:i:s') . "] Bắt đầu xử lý hàng đợi Email...\n";

try {
  $store = new EmailJobStore();
  $mailService = new MailService($store);

  // Lấy 10 jobs đang chờ và tự động khóa chúng sang trạng thái processing
  $jobs = $store->getAndLockPendingJobs(10);

  if (empty($jobs)) {
    echo "[" . date('Y-m-d H:i:s') . "] Hàng đợi trống. Kết thúc.\n";
    exit(0);
  }

  echo "[" . date('Y-m-d H:i:s') . "] Đã lấy " . count($jobs) . " jobs để xử lý.\n";

  $successCount = 0;
  $failCount = 0;

  foreach ($jobs as $job) {
    try {
      echo "- Đang gửi cho: {$job['to_email']}... ";

      $isSent = $mailService->sendRaw(
        $job['to_email'],
        $job['to_name'],
        $job['subject'],
        $job['body']
      );

      if ($isSent) {
        $store->updateJobStatus($job['id'], 'completed');
        echo "Thành công!\n";
        $successCount++;
      } else {
        $store->updateJobStatus($job['id'], 'failed', 'MailService returned false (Empty credentials?)');
        echo "Thất bại (credentials chưa cấu hình?).\n";
        $failCount++;
      }
    } catch (Exception $e) {
      $errorMessage = $e->getMessage();
      echo "Lỗi: {$errorMessage}\n";

      // Nếu số lần thử đã lớn hơn 3, ta chuyển thành failed vĩnh viễn
      if ($job['attempts'] >= 2) {
        $store->updateJobStatus($job['id'], 'failed', $errorMessage);
      } else {
        // Trả về pending để thử lại lần sau
        $store->updateJobStatus($job['id'], 'pending', "Lỗi tạm thời: $errorMessage");
      }
      $failCount++;
    }
  }

  echo "[" . date('Y-m-d H:i:s') . "] Hoàn thành. Thành công: $successCount, Thất bại: $failCount.\n";
} catch (Exception $e) {
  echo "[" . date('Y-m-d H:i:s') . "] LỖI NGHIÊM TRỌNG: " . $e->getMessage() . "\n";
  error_log("Lỗi Worker process_email_queue: " . $e->getMessage());
  exit(1);
}
