<?php

namespace App\Stores;

use App\Core\Schema\Compiler\MySQLCompiler;
use App\Core\Schema\QueryBuilder;
use App\Core\Store;
use App\Models\Company;
use PDO;
use Exception;

interface ICompanyStore
{
  /** @return Company[] */
  public function getPaginated(int $pageTo, int $limit = 15, string $filter = 'all'): array;
  public function getById(int $id): ?Company;
  public function upsertFromApi(array $data): int;
  public function createManual(array $data): int;
  public function suggestByNameAndVerified(string $query): array;
  public function searchForMerge(string $query, int $excludeId): array;
  public function update(Company $company): bool;
  public function softDelete(int $id): bool;
  public function getTotalCount(string $filter = 'all'): int;
  public function getCountByVerified(int $isVerified): int;
  public function findByTaxCode(string $taxCode): ?array;
  public function findDuplicates(): array;
  public function mergeCompanies(int $sourceId, int $targetId, array $mergedFields): bool;
  public function bulkApprove(array $ids): int;
  public function getRelatedCounts(int $companyId): array;
}

class CompanyStore extends Store implements ICompanyStore
{
  public function findByTaxCode(string $taxCode): ?array
  {
    $sql = "SELECT * FROM companies WHERE tax_code = :tax_code AND deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':tax_code' => $taxCode]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getById(int $id): ?Company
  {
    $sql = "SELECT * FROM companies WHERE id = :id AND deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? Company::fromArray($result) : null;
  }

  public function upsertFromApi(array $data): int
  {
    $taxCode = $data['tax_code'];
    $existing = $this->findByTaxCode($taxCode);

    if ($existing) {
      $sql = "UPDATE companies SET 
                name = :name, 
                normalized_name = :normalized_name, 
                address = :address, 
                is_verified = 1,
                source = 'api',
                updated_at = NOW() 
              WHERE id = :id";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        ':id' => $existing['id'],
        ':name' => $data['name'],
        ':normalized_name' => $data['normalized_name'] ?? null,
        ':address' => $data['address'] ?? null,
      ]);
      return $existing['id'];
    } else {
      $sql = "INSERT INTO companies (tax_code, name, normalized_name, address, is_verified, source, created_at, updated_at) 
              VALUES (:tax_code, :name, :normalized_name, :address, 1, 'api', NOW(), NOW())";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        ':tax_code' => $taxCode,
        ':name' => $data['name'],
        ':normalized_name' => $data['normalized_name'] ?? null,
        ':address' => $data['address'] ?? null,
      ]);
      return (int)$this->db->lastInsertId();
    }
  }

  public function createManual(array $data): int
  {
    $sql = "INSERT INTO companies (tax_code, name, normalized_name, address, is_verified, source, created_at, updated_at) 
            VALUES (:tax_code, :name, :normalized_name, :address, 0, 'manual', NOW(), NOW())";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':tax_code' => $data['tax_code'] ?? null,
      ':name' => $data['name'],
      ':normalized_name' => $data['normalized_name'] ?? null,
      ':address' => $data['address'] ?? null,
    ]);
    return (int)$this->db->lastInsertId();
  }

  /**
   * Gợi ý công ty theo tên, chỉ trả về các công ty đã được xác thực
   * 
   * @param string $query Chuỗi tìm kiếm
   * @return array
   */
  public function suggestByNameAndVerified(string $query): array
  {
    $sql = "SELECT id, tax_code, name, address FROM companies 
            WHERE (name LIKE :query) 
            AND deleted_at IS NULL 
            AND is_verified = 1
            LIMIT 20";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':query' => "%$query%"]);

    return array_map(fn($row) => Company::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /** @return Company[] */
  public function getPaginated(int $pageTo, int $limit = 15, string $filter = 'all'): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('companies')
      ->select('*')
      ->is('deleted_at', null)
      ->order('id', ['ascending' => false])
      ->range(($pageTo - 1) * $limit, $pageTo * $limit - 1);

    if ($filter === 'pending') {
      $query->eq('is_verified', 0);
    } elseif ($filter === 'verified') {
      $query->eq('is_verified', 1);
    }

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(fn($row) => Company::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /**
   * Lấy tổng số lượng record
   */
  public function getTotalCount(string $filter = 'all'): int
  {
    $sql = "SELECT COUNT(*) FROM `companies` WHERE `deleted_at` IS NULL";

    if ($filter === 'pending') {
      $sql .= " AND is_verified = 0";
    } elseif ($filter === 'verified') {
      $sql .= " AND is_verified = 1";
    }

    $stmt = $this->db->query($sql);
    return (int) $stmt->fetchColumn();
  }

  public function getCountByVerified(int $isVerified): int
  {
    $sql = "SELECT COUNT(*) FROM `companies` WHERE `deleted_at` IS NULL AND is_verified = :is_verified";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':is_verified' => $isVerified]);
    return (int) $stmt->fetchColumn();
  }

  public function searchForMerge(string $query, int $excludeId): array
  {
    $sql = "SELECT * FROM companies 
            WHERE (name LIKE :query1 OR tax_code LIKE :query2) 
            AND id != :exclude_id
            AND deleted_at IS NULL 
            LIMIT 20";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':query1' => "%$query%", ':query2' => "%$query%", ':exclude_id' => $excludeId]);

    return array_map(fn($row) => Company::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function findDuplicates(): array
  {
    // Tìm các công ty nghi ngờ trùng lặp (cùng normalized_name hoặc cùng tax_code)
    $sql = "
        SELECT 
            c1.id as c1_id, c1.name as c1_name, c1.tax_code as c1_tax, c1.is_verified as c1_verified,
            c2.id as c2_id, c2.name as c2_name, c2.tax_code as c2_tax, c2.is_verified as c2_verified,
            CASE 
                WHEN c1.tax_code = c2.tax_code AND c1.tax_code IS NOT NULL AND c1.tax_code != '' THEN 'Cùng MST'
                WHEN c1.normalized_name = c2.normalized_name AND c1.normalized_name IS NOT NULL AND c1.normalized_name != '' THEN 'Trùng tên'
                ELSE 'Tên tương tự'
            END as reason
        FROM companies c1
        JOIN companies c2 ON c1.id < c2.id 
            AND c1.deleted_at IS NULL AND c2.deleted_at IS NULL
            AND (
                (c1.tax_code = c2.tax_code AND c1.tax_code IS NOT NULL AND c1.tax_code != '') OR 
                (c1.normalized_name = c2.normalized_name AND c1.normalized_name IS NOT NULL AND c1.normalized_name != '') OR
                (c1.normalized_name LIKE CONCAT(c2.normalized_name, '%') AND LENGTH(c2.normalized_name) > 10) OR
                (c2.normalized_name LIKE CONCAT(c1.normalized_name, '%') AND LENGTH(c1.normalized_name) > 10)
            )
        LIMIT 50
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getRelatedCounts(int $companyId): array
  {
    $studentsCount = 0;
    $referralsCount = 0;

    $stmt1 = $this->db->prepare("SELECT COUNT(*) FROM internship_batch_students WHERE company_id = :id");
    $stmt1->execute([':id' => $companyId]);
    $studentsCount = (int)$stmt1->fetchColumn();

    try {
      $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM referral_letters WHERE company_id = :id");
      $stmt2->execute([':id' => $companyId]);
      $referralsCount = (int)$stmt2->fetchColumn();
    } catch (Exception $e) {
      $referralsCount = 0;
    }

    return [
      'students' => $studentsCount,
      'referral_letters' => $referralsCount
    ];
  }

  public function bulkApprove(array $ids): int
  {
    if (empty($ids)) return 0;

    $in = str_repeat('?,', count($ids) - 1) . '?';
    $sql = "UPDATE companies SET is_verified = 1, updated_at = NOW() WHERE id IN ($in) AND deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($ids);

    return $stmt->rowCount();
  }

  public function mergeCompanies(int $sourceId, int $targetId, array $mergedFields): bool
  {
    try {
      $this->db->beginTransaction();

      $fields = [];
      $values = [];
      foreach ($mergedFields as $key => $val) {
        $fields[] = "$key = ?";
        $values[] = $val;
      }
      $fields[] = "updated_at = NOW()";

      $sqlTarget = "UPDATE companies SET " . implode(', ', $fields) . " WHERE id = ?";
      $values[] = $targetId;

      $stmtTarget = $this->db->prepare($sqlTarget);
      $stmtTarget->execute($values);

      $stmtFk1 = $this->db->prepare("UPDATE internship_batch_students SET company_id = :target WHERE company_id = :source");
      $stmtFk1->execute([':target' => $targetId, ':source' => $sourceId]);

      try {
        $stmtFk2 = $this->db->prepare("UPDATE referral_letters SET company_id = :target WHERE company_id = :source");
        $stmtFk2->execute([':target' => $targetId, ':source' => $sourceId]);
      } catch (Exception $e) {
      }

      $stmtDelete = $this->db->prepare("UPDATE companies SET deleted_at = NOW(), updated_at = NOW() WHERE id = :source");
      $stmtDelete->execute([':source' => $sourceId]);

      $this->db->commit();
      return true;
    } catch (Exception $e) {
      $this->db->rollBack();
      throw $e;
    }
  }

  /**
   * Cập nhật thông tin công ty
   * 
   * @param Company $company
   * @return bool
   */
  public function update(Company $company): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $fields = [
      'name' => $company->name,
      'tax_code' => $company->tax_code,
      'address' => $company->address,
      'phone' => $company->phone,
      'email' => $company->email,
      'website' => $company->website,
      'note' => $company->note,
      'updated_at' => date('Y-m-d H:i:s'),
    ];

    $query = $builder
      ->from('companies')
      ->eq('id', $company->id)
      ->update($fields);

    $stmt = $this->db->prepare($query->toSql());
    $success = $stmt->execute($query->getBindings());

    if (!$success) {
      throw new \Exception('Không thể cập nhật công ty trong cơ sở dữ liệu.');
    }

    return true;
  }

  /**
   * Xóa mềm công ty
   * 
   * @param int $id
   * @return bool
   */
  public function softDelete($id): bool
  {
    $sql = "
      UPDATE `companies`
      SET
        `deleted_at` = NOW()
      WHERE `id` = :id;
    ";
    $stmt = $this->db->prepare($sql);

    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
      throw new \Exception("Không tìm thấy công ty mã: " . $id);
    }

    return true;
  }
}
