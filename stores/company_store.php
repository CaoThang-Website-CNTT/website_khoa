<?php

namespace App\Stores;

use App\Core\Store;
use App\Models\Company;
use PDO;

class CompanyStore extends Store
{
  public function findByTaxCode(string $taxCode): ?array
  {
    $sql = "SELECT * FROM companies WHERE tax_code = :tax_code AND deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':tax_code' => $taxCode]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getById(int $id): ?array
  {
    $sql = "SELECT * FROM companies WHERE id = :id AND deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
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
}
