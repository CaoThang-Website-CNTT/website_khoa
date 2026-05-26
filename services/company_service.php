<?php

namespace App\Services;

use App\Stores\CompanyStore;
use App\Models\Company;
use Exception;
use App\Core\Pageable;

interface ICompanyService
{
  /** @return Pageable */
  public function getCompanies(int $page, int $limit = 15): Pageable;
  //public function createCompanies(array $data): ?Company;
  public function getCompanyById(int $id): ?Company;
  public function findByTaxCode(string $taxCode): ?array;
  public function upsertFromApi(array $data): int;
  public function createManual(array $data): int;
  public function suggestByName(string $query): array;
  public function updateCompany(int $id, array $data): bool;
  public function deleteCompany(int $id): bool;
}

class CompanyService implements ICompanyService
{
  private CompanyStore $_store;

  public function __construct(CompanyStore $store)
  {
    $this->_store = $store;
  }

  public function getCompanyById(int $id): ?Company
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

  public function getCompanies(int $page, int $limit = 15): Pageable
  {
    $companies = $this->_store->getPaginated($page, $limit);
    $total = $this->_store->getTotalCount();
    return new Pageable($companies, $total, $limit, $page);
  }

  public function updateCompany(int $id, array $data): bool
  {
    $company = $this->_store->getById($id);
    if (!$company) {
      throw new Exception('Không tồn tại công ty này');
    }

    $company->name = $data['company_name'] ?? $company->name;
    $company->tax_code = $data['tax_code'] ?? $company->tax_code;
    $company->phone = $data['phone'] ?? $company->phone;
    $company->address = $data['address'] ?? $company->address;
    $company->email = $data['email'] ?? $company->email;
    $company->website = $data['website'] ?? $company->website;
    $company->note = $data['note'] ?? $company->note;

    return $this->_store->update($company);
  }

  public function deleteCompany(int $id): bool
  {
    return $this->_store->softDelete($id);
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
