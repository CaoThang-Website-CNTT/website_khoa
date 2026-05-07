<?php

namespace App\Services;

use App\Stores\CompanyStore;

class CompanyService
{
  private CompanyStore $_store;

  public function __construct(CompanyStore $store)
  {
    $this->_store = $store;
  }

  public function getById(int $id): ?array
  {
    return $this->_store->getById($id);
  }

  public function findByTaxCode(string $taxCode): ?array
  {
    return $this->_store->findByTaxCode($taxCode);
  }

  public function upsertFromApi(array $data): int
  {
    // Chuẩn hóa tên trước khi lưu
    $data['normalized_name'] = $this->normalizeName($data['name']);
    return $this->_store->upsertFromApi($data);
  }

  public function createManual(array $data): int
  {
    $data['normalized_name'] = $this->normalizeName($data['name']);
    return $this->_store->createManual($data);
  }

  public function suggestByName(string $query): array
  {
    return $this->_store->suggestByNameAndVerified($query);
  }

  /**
   * Chuẩn hóa tên công ty
   * 
   * @param string $name Tên công ty
   * @return string
   */
  private function normalizeName(string $name): string
  {
    return mb_strtolower(trim($name), 'UTF-8');
  }
}
