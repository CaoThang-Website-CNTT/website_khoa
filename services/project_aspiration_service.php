<?php

namespace App\Services;

use App\Stores\ProjectAspirationStore;

interface IProjectAspirationService
{
  public function addAspirations(int $groupId, array $topicIds): bool;
  public function getAspirationsByGroup(int $groupId): array;
  public function updateAspirationStatus(int $id, string $status): bool;
  public function updateStatusByGroupAndTopic(int $groupId, int $topicId, string $status): bool;
  public function getAspirationsByBatch(int $batchId): array;
}

class ProjectAspirationService implements IProjectAspirationService
{
  private ProjectAspirationStore $_store;

  public function __construct(ProjectAspirationStore $store)
  {
    $this->_store = $store;
  }

  public function addAspirations(int $groupId, array $topicIds): bool
  {
    return $this->_store->addAspirations($groupId, $topicIds);
  }

  public function getAspirationsByGroup(int $groupId): array
  {
    return $this->_store->getAspirationsByGroup($groupId);
  }

  public function updateAspirationStatus(int $id, string $status): bool
  {
    return $this->_store->updateAspirationStatus($id, $status);
  }

  public function updateStatusByGroupAndTopic(int $groupId, int $topicId, string $status): bool
  {
    return $this->_store->updateStatusByGroupAndTopic($groupId, $topicId, $status);
  }

  public function getAspirationsByBatch(int $batchId): array
  {
    return $this->_store->getAspirationsByBatch($batchId);
  }
}
