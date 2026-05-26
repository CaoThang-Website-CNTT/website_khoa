<?php

namespace App\Stores;

use App\Core\Schema\Compiler\MySQLCompiler;
use App\Core\Schema\QueryBuilder;
use App\Core\Store;
use App\Models\ReferralLetter;
use Override;
use PDO;

interface IReferralLetterStore
{
  public function create(array $referralLetter): int;
  public function getTotalCount(): int;
  public function getAllByBatchStudentId(int $batchStudentId): array;
  public function getById(int $id): ?ReferralLetter;
  public function getByIdWithCompany(int $id): ?array;
  public function getLettersWithCompanyByBatchStudentId(int $batchStudentId): array;
  public function getPaginated(int $pageTo, int $limit = 15): array;
  public function updateStatus(int $id, string $status, array $extraData = []): bool;
  public function updateCompanyId(int $id, int $companyId): bool;
  public function getAllWithDetailsByBatchId(int $batchId): array;
  public function getByIds(array $ids): array;
}

class ReferralLetterStore extends Store implements IReferralLetterStore
{
  public function getById(int $id): ?ReferralLetter
  {
    $sql = "SELECT * FROM referral_letters WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? ReferralLetter::fromArray($result) : null;
  }

  public function getByIdWithCompany(int $id): ?array
  {
    $sql = "
      SELECT rl.*, 
             c.name as company_name, c.tax_code as company_tax_code, c.address as company_address
      FROM referral_letters rl
      LEFT JOIN companies c ON rl.company_id = c.id
      WHERE rl.id = :id
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getAllByBatchStudentId(int $batchStudentId): array
  {
    $sql = "SELECT * FROM referral_letters WHERE batch_student_id = :batch_student_id ORDER BY created_at DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_student_id' => $batchStudentId]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_map(fn($item) => ReferralLetter::fromArray($item), $result);
  }

  public function getLettersWithCompanyByBatchStudentId(int $batchStudentId): array
  {
    $sql = "
      SELECT rl.*, 
             c.name as company_name, c.tax_code as company_tax_code, c.address as company_address
      FROM referral_letters rl
      LEFT JOIN companies c ON rl.company_id = c.id
      WHERE rl.batch_student_id = :batch_student_id
      ORDER BY rl.created_at DESC
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_student_id' => $batchStudentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getAllWithDetailsByBatchId(int $batchId): array
  {
    $sql = "
      SELECT rl.*, 
             c.name as company_name, c.tax_code as company_tax_code, c.address as company_address, c.is_verified as company_is_verified,
             s.student_id as student_code, s.full_name as student_full_name, cl.short_name as classroom_name
      FROM referral_letters rl
      JOIN internship_batch_students bs ON rl.batch_student_id = bs.id
      JOIN students s ON bs.student_id = s.id
      LEFT JOIN classrooms cl ON s.classroom_id = cl.id
      LEFT JOIN companies c ON rl.company_id = c.id
      WHERE bs.batch_id = :batch_id
      ORDER BY rl.created_at DESC
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getByIds(array $ids): array
  {
    if (empty($ids)) return [];

    $inClause = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT * FROM referral_letters WHERE id IN ($inClause)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($ids);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_map(fn($item) => ReferralLetter::fromArray($item), $result);
  }

  /**
   * Lấy tổng số lượng record
   * @return int
   */
  public function getTotalCount(): int
  {
    $sql = "
      SELECT COUNT(*) 
      FROM `referral_letters`
    ";

    $stmt = $this->db->query($sql);
    return (int) $stmt->fetchColumn();
  }

  /**
   * Thêm một record mới
   * @param array $referralLetter
   * @return int
   */
  public function create(array $referralLetter): int
  {
    $sql = "INSERT INTO referral_letters (batch_student_id, company_id, status, 
            cancel_reason, printed_at, processed_by, created_at, updated_at)
            VALUES (:batch_student_id, :company_id, :status, :cancel_reason, :printed_at, :processed_by, NOW(), NOW())";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':batch_student_id' => $referralLetter['batch_student_id'] ?? null,
      ':company_id'       => $referralLetter['company_id'] ?? null,
      ':status'           => $referralLetter['status'] ?? 'pending',
      ':cancel_reason'    => $referralLetter['cancel_reason'] ?? null,
      ':printed_at'       => $referralLetter['printed_at'] ?? null,
      ':processed_by'     => $referralLetter['processed_by'] ?? null,
    ]);
    return (int) $this->db->lastInsertId();
  }

  /**
   * Lấy danh sách có phân trang
   * @param int $pageTo
   * @param int $limit
   * 
   * @return array
   */
  public function getPaginated(int $pageTo, int $limit = 15): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('referral_letters')
      ->select('*')
      ->range(($pageTo - 1) * $limit, $pageTo * $limit - 1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute();

    return array_map(fn($row) => ReferralLetter::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /**
   * Cập nhật trạng thái
   * 
   * @param int $id
   * @param string $status
   * @param array $extraData
   * 
   * @return bool
   */
  public function updateStatus(int $id, string $status, array $extraData = []): bool
  {
    $fields = ["status = :status"];
    $params = [':id' => $id, ':status' => $status];

    if (isset($extraData['printed_at'])) {
      $fields[] = "printed_at = :printed_at";
      $params[':printed_at'] = $extraData['printed_at'];
    }

    if (isset($extraData['processed_by'])) {
      $fields[] = "processed_by = :processed_by";
      $params[':processed_by'] = $extraData['processed_by'];
    }

    if (array_key_exists('cancel_reason', $extraData)) {
      $fields[] = "cancel_reason = :cancel_reason";
      $params[':cancel_reason'] = $extraData['cancel_reason'];
    }

    $sql = "UPDATE referral_letters SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
  }

  public function updateCompanyId(int $id, int $companyId): bool
  {
    $sql = "UPDATE referral_letters SET company_id = :company_id, updated_at = NOW() WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      ':company_id' => $companyId,
      ':id'         => $id
    ]);
  }
}
