<?php

namespace App\Stores;

use App\Core\Schema\Compiler\MySQLCompiler;
use App\Core\Schema\QueryBuilder;
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
  public function getPaginated(int $pageTo, int $limit = 15): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('companies')
      ->select('*')
      ->is('deleted_at', null)
      ->range(($pageTo - 1) * $limit, $pageTo * $limit - 1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute();

    return array_map(fn($row) => Company::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /**
   * Lấy tổng số lượng record
   */
  public function getTotalCount(): int
  {
    $sql = "
      SELECT COUNT(*) 
      FROM `companies`
      WHERE `deleted_at` IS NULL
    ";

    $stmt = $this->db->query($sql);
    return (int) $stmt->fetchColumn();
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
}
