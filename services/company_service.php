<?php

namespace App\Services;

use App\Stores\CompanyStore;
use App\Models\Company;
use Exception;
use App\Core\Pageable;

interface ICompanyService
{
  /** @return Pageable */
  public function getCompanies(int $page, int $limit = 15, string $filter = 'all'): Pageable;
  //public function createCompanies(array $data): ?Company;
  public function getCompanyById(int $id): ?Company;
  public function findByTaxCode(string $taxCode): ?array;
  public function upsertFromApi(array $data): int;
  public function createManual(array $data): int;
  public function suggestByName(string $query): array;
  public function updateCompany(int $id, array $data): bool;
  public function deleteCompany(int $id): bool;
  public function getCountByVerified(int $isVerified): int;
  public function approve(int $id): bool;
  public function bulkApprove(array $ids): int;
  public function merge(int $sourceId, int $targetId, array $selectedFields): bool;
  public function quickMerge(int $sourceId, int $targetId): bool;
  public function bulkQuickMerge(array $sourceIds, int $targetId): array;
  public function findDuplicates(): array;
  public function getGroupedDuplicates(): array;
  public function searchForMerge(string $query, int $excludeId): array;
  public function getRelatedCounts(int $companyId): array;
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
    $data['is_verified'] = $data['is_verified'] ?? 0;
    return $this->_store->createManual($data);
  }

  public function suggestByName(string $query): array
  {
    return $this->_store->suggestByNameAndVerified($query);
  }

  public function getCompanies(int $page, int $limit = 15, string $filter = 'all'): Pageable
  {
    $companies = $this->_store->getPaginated($page, $limit, $filter);
    $total = $this->_store->getTotalCount($filter);
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
    $counts = $this->_store->getRelatedCounts($id);
    if ($counts['students'] > 0 || $counts['referral_letters'] > 0) {
      throw new Exception("Không thể xóa công ty này vì đang có sinh viên hoặc giấy giới thiệu tham chiếu. Vui lòng gộp vào công ty khác trước.");
    }
    return $this->_store->softDelete($id);
  }

  public function getCountByVerified(int $isVerified): int
  {
    return $this->_store->getCountByVerified($isVerified);
  }

  public function approve(int $id): bool
  {
    $company = $this->_store->getById($id);
    if (!$company) throw new Exception('Không tồn tại công ty này');
    $this->_store->bulkApprove([$id]);
    return true;
  }

  public function bulkApprove(array $ids): int
  {
    return $this->_store->bulkApprove($ids);
  }

  public function findDuplicates(): array
  {
    return $this->_store->findDuplicates();
  }

  public function getGroupedDuplicates(): array
  {
    $pairs = $this->_store->findDuplicates();
    $adj = [];
    $nodes = [];

    foreach ($pairs as $pair) {
      $c1 = [
        'id' => $pair['c1_id'],
        'name' => $pair['c1_name'],
        'tax_code' => $pair['c1_tax'],
        'is_verified' => $pair['c1_verified']
      ];
      $c2 = [
        'id' => $pair['c2_id'],
        'name' => $pair['c2_name'],
        'tax_code' => $pair['c2_tax'],
        'is_verified' => $pair['c2_verified']
      ];

      $nodes[$c1['id']] = $c1;
      $nodes[$c2['id']] = $c2;

      $adj[$c1['id']][$c2['id']] = $pair['reason'];
      $adj[$c2['id']][$c1['id']] = $pair['reason'];
    }

    $visited = [];
    $groups = [];

    foreach ($nodes as $id => $node) {
      if (isset($visited[$id])) continue;

      $component = [];
      $queue = [$id];
      $visited[$id] = true;

      while (!empty($queue)) {
        $curr = array_shift($queue);
        $component[] = $nodes[$curr];

        if (isset($adj[$curr])) {
          foreach ($adj[$curr] as $neighborId => $reason) {
            if (!isset($visited[$neighborId])) {
              $visited[$neighborId] = true;
              $queue[] = $neighborId;
            }
          }
        }
      }

      usort($component, function ($a, $b) {
        if ($a['is_verified'] !== $b['is_verified']) {
          return $b['is_verified'] <=> $a['is_verified'];
        }
        return $a['id'] <=> $b['id'];
      });

      $parent = array_shift($component);

      $children = [];
      foreach ($component as $child) {
        $reason = $adj[$parent['id']][$child['id']] ?? 'Trùng lặp (Liên đới)';
        $child['reason'] = $reason;
        $children[] = $child;
      }

      if (!empty($children)) {
        $groups[] = [
          'parent' => $parent,
          'children' => $children
        ];
      }
    }

    return $groups;
  }

  public function quickMerge(int $sourceId, int $targetId): bool
  {
    if ($sourceId === $targetId) {
      throw new Exception('Không thể gộp một công ty vào chính nó.');
    }
    $source = $this->_store->getById($sourceId);
    $target = $this->_store->getById($targetId);
    if (!$source || !$target) {
      throw new Exception('Công ty nguồn hoặc đích không tồn tại.');
    }
    // Gộp nhanh chỉ lấy thông tin của target (đích), không cần trường nào khác
    return $this->_store->mergeCompanies($sourceId, $targetId, []);
  }

  public function bulkQuickMerge(array $sourceIds, int $targetId): array
  {
    $results = ['success' => 0, 'failed' => 0];
    foreach ($sourceIds as $sourceId) {
      try {
        $this->quickMerge($sourceId, $targetId);
        $results['success']++;
      } catch (Exception $e) {
        $results['failed']++;
      }
    }
    return $results;
  }

  public function merge(int $sourceId, int $targetId, array $selectedFields): bool
  {
    if ($sourceId === $targetId) {
      throw new Exception('Không thể gộp một công ty vào chính nó.');
    }
    $source = $this->_store->getById($sourceId);
    $target = $this->_store->getById($targetId);
    if (!$source || !$target) {
      throw new Exception('Công ty nguồn hoặc đích không tồn tại.');
    }
    return $this->_store->mergeCompanies($sourceId, $targetId, $selectedFields);
  }

  public function searchForMerge(string $query, int $excludeId): array
  {
    return $this->_store->searchForMerge($query, $excludeId);
  }

  public function getRelatedCounts(int $companyId): array
  {
    return $this->_store->getRelatedCounts($companyId);
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
