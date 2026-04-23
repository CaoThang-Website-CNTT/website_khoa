<?php

namespace App\Services;

use App\Stores\InternshipBatchStore;
use Database;

class InternshipBatchService
{
  private InternshipBatchStore $_store;

  public function __construct(InternshipBatchStore $store)
  {
    $this->_store = $store;
  }

  /**
   * Tạo toàn bộ đợt thực tập trong 1 transaction (Batch, Classrooms, Students, Supervisors)
   */
  public function createFullBatch(array $batchData, array $studentIds, array $supervisors, array $classroomIds, int $adminId): int
  {
    return Database::getInstance()->transaction(function () use ($batchData, $studentIds, $supervisors, $classroomIds, $adminId) {
      
      $batchData['created_by'] = $adminId;
      $batchId = $this->_store->createBatch($batchData);

      if (!$batchId) {
        throw new \Exception('Không thể tạo thông tin đợt thực tập.');
      }

      $this->_store->addClassroomsToBatch($batchId, $classroomIds);
      $this->_store->addStudentsToBatch($batchId, $studentIds);
      $this->_store->addSupervisorsToBatch($batchId, $supervisors);

      return $batchId;
    });
  }
  
  public function getEligibleStudentsByClassroom(int $classroomId): array
  {
     return $this->_store->getEligibleStudentsByClassroom($classroomId);
  }
  
  public function getActiveTeachers(): array
  {
     return $this->_store->getActiveTeachers();
  }

  public function getAllClassrooms(): array
  {
     return $this->_store->getAllClassrooms();
  }
}
