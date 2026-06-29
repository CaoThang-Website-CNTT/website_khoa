<?php

namespace App\Stores;

use App\Core\Store;
use PDO;
use Exception;

interface IEmailJobStore
{
  public function pushJob(string $toEmail, ?string $toName, string $subject, string $body): int;
  public function getAndLockPendingJobs(int $limit = 10): array;
  public function updateJobStatus(int $id, string $status, ?string $errorMessage = null): bool;
}

class EmailJobStore extends Store implements IEmailJobStore
{
  /**
   * Thêm một Job mới vào hàng đợi
   */
  public function pushJob(string $toEmail, ?string $toName, string $subject, string $body): int
  {
    $sql = "INSERT INTO email_jobs (to_email, to_name, subject, body, status, created_at, updated_at) 
            VALUES (:to_email, :to_name, :subject, :body, 'pending', NOW(), NOW())";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':to_email' => $toEmail,
      ':to_name'  => $toName,
      ':subject'  => $subject,
      ':body'     => $body
    ]);

    return (int) $this->db->lastInsertId();
  }

  /**
   * Lấy ra $limit jobs đang chờ và lập tức đổi trạng thái sang `processing`
   */
  public function getAndLockPendingJobs(int $limit = 10): array
  {
    $this->db->beginTransaction();

    try {
      // Tìm các job đang pending
      // Đối với MySQL/MariaDB cũ không hỗ trợ FOR UPDATE SKIP LOCKED -> lấy ID trước, sau đó update.
      $sql = "SELECT id, to_email, to_name, subject, body, attempts 
              FROM email_jobs 
              WHERE status = 'pending' 
              ORDER BY created_at ASC 
              LIMIT :limit";

      $stmt = $this->db->prepare($sql);
      $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
      $stmt->execute();

      $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (empty($jobs)) {
        $this->db->rollBack();
        return [];
      }

      $jobIds = array_column($jobs, 'id');
      $inQuery = implode(',', array_fill(0, count($jobIds), '?'));

      $updateSql = "UPDATE email_jobs 
                    SET status = 'processing', updated_at = NOW() 
                    WHERE id IN ($inQuery) AND status = 'pending'";
      $updateStmt = $this->db->prepare($updateSql);
      $updateStmt->execute($jobIds);

      $this->db->commit();

      return $jobs;
    } catch (Exception $e) {
      $this->db->rollBack();
      throw $e;
    }
  }

  /**
   * Cập nhật kết quả gửi email
   */
  public function updateJobStatus(int $id, string $status, ?string $errorMessage = null): bool
  {
    $sql = "UPDATE email_jobs 
            SET status = :status, 
                error_message = :error_message, 
                attempts = attempts + 1,
                updated_at = NOW() 
            WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      ':status' => $status,
      ':error_message' => $errorMessage,
      ':id' => $id
    ]);
  }
}
