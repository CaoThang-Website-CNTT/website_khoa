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
  public function getWithStudentsByLetterId(int $id): ?array;
  public function getForPrint(int $id): ?array;
  public function updatePrintInfo(int $id, array $printData): bool;
  public function getByIds(array $ids): array;
}

class ReferralLetterStore extends Store implements IReferralLetterStore
{
  public function getById(int $id): ?ReferralLetter
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('referral_letters')->select('*')->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
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
             s.student_id as student_code, s.full_name as student_full_name, cl.short_name as classroom_name,
             t.full_name as teacher_name,
             (SELECT COUNT(*) FROM referral_letter_students rls WHERE rls.referral_letter_id = rl.id) as student_count
      FROM referral_letters rl
      LEFT JOIN internship_batch_students bs ON rl.batch_student_id = bs.id
      LEFT JOIN students s ON bs.student_id = s.id
      LEFT JOIN classrooms cl ON s.classroom_id = cl.id
      LEFT JOIN companies c ON rl.company_id = c.id
      LEFT JOIN teachers t ON rl.teacher_id = t.id
      WHERE rl.id IN (
        SELECT DISTINCT rls.referral_letter_id 
        FROM referral_letter_students rls 
        JOIN internship_batch_students ibs ON rls.batch_student_id = ibs.id 
        WHERE ibs.batch_id = :batch_id1
      ) OR bs.batch_id = :batch_id2
      ORDER BY rl.created_at DESC
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id1' => $batchId, ':batch_id2' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getWithStudentsByLetterId(int $id): ?array
  {
    $letter = $this->getByIdWithCompany($id);
    if (!$letter) return null;
    
    $stmt = $this->db->prepare("SELECT * FROM referral_letter_students WHERE referral_letter_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$id]);
    $letter['students'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $letter;
  }

  public function getForPrint(int $id): ?array
  {
    $sql = "
      SELECT rl.*, 
             COALESCE(ibs.batch_id, (
                 SELECT ibs2.batch_id 
                 FROM referral_letter_students rls 
                 JOIN internship_batch_students ibs2 ON rls.batch_student_id = ibs2.id 
                 WHERE rls.referral_letter_id = rl.id 
                 LIMIT 1
             )) as batch_id,
             c.name as company_name, c.tax_code as company_tax_code, c.address as company_address,
             t.full_name as teacher_name
      FROM referral_letters rl
      LEFT JOIN internship_batch_students ibs ON rl.batch_student_id = ibs.id
      LEFT JOIN companies c ON rl.company_id = c.id
      LEFT JOIN teachers t ON rl.teacher_id = t.id
      WHERE rl.id = :id
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $letter = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$letter) return null;
    
    $stmt = $this->db->prepare("SELECT * FROM referral_letter_students WHERE referral_letter_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$id]);
    $letter['students'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $letter;
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
    $now = date('Y-m-d H:i:s');
    $query = (new QueryBuilder(new MySQLCompiler()))->from('referral_letters')->insert([
      'batch_student_id' => $referralLetter['batch_student_id'] ?? null,
      'company_id' => $referralLetter['company_id'] ?? null,
      'teacher_id' => $referralLetter['teacher_id'] ?? null,
      'status' => $referralLetter['status'] ?? 'pending',
      'cancel_reason' => $referralLetter['cancel_reason'] ?? null,
      'reviewed_at' => $referralLetter['reviewed_at'] ?? null,
      'internship_start_date' => $referralLetter['internship_start_date'] ?? null,
      'internship_end_date' => $referralLetter['internship_end_date'] ?? null,
      'document_number' => $referralLetter['document_number'] ?? null,
      'note' => $referralLetter['note'] ?? null,
      'printed_at' => $referralLetter['printed_at'] ?? null,
      'processed_by' => $referralLetter['processed_by'] ?? null,
      'cancelled_by' => $referralLetter['cancelled_by'] ?? null,
      'created_at' => $now, 'updated_at' => $now,
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
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

    if (array_key_exists('reviewed_at', $extraData)) {
      $fields[] = "reviewed_at = :reviewed_at";
      $params[':reviewed_at'] = $extraData['reviewed_at'];
    }

    if (array_key_exists('cancelled_by', $extraData)) {
      $fields[] = "cancelled_by = :cancelled_by";
      $params[':cancelled_by'] = $extraData['cancelled_by'];
    }

    $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
    foreach (['printed_at', 'processed_by', 'cancel_reason', 'reviewed_at', 'cancelled_by'] as $key) {
      if (array_key_exists($key, $extraData)) $data[$key] = $extraData[$key];
    }
    $query = (new QueryBuilder(new MySQLCompiler()))->from('referral_letters')->update($data)->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
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

  public function updatePrintInfo(int $id, array $printData): bool
  {
    $fields = [];
    $params = [':id' => $id];

    if (array_key_exists('internship_start_date', $printData)) {
      $fields[] = "internship_start_date = :internship_start_date";
      $params[':internship_start_date'] = $printData['internship_start_date'];
    }
    if (array_key_exists('internship_end_date', $printData)) {
      $fields[] = "internship_end_date = :internship_end_date";
      $params[':internship_end_date'] = $printData['internship_end_date'];
    }
    if (array_key_exists('document_number', $printData)) {
      $fields[] = "document_number = :document_number";
      $params[':document_number'] = $printData['document_number'];
    }
    
    if (empty($fields)) return true;

    $sql = "UPDATE referral_letters SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
  }
}
