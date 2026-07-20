<?php

namespace App\Stores;

use App\Core\AppTime;

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use PDO;

interface IProjectAspirationStore
{
  public function addAspirations(int $groupId, array $topicIds): bool;
  public function getAspirationsByGroup(int $groupId): array;
  public function updateAspirationStatus(int $id, string $status): bool;
  public function updateStatusByGroupAndTopic(int $groupId, int $topicId, string $status): bool;
  public function getAspirationsByBatch(int $batchId): array;
  public function lockAspirations(int $groupId): bool;
  public function unlockAspirations(int $groupId): bool;
  public function isLocked(int $groupId): bool;
}

class ProjectAspirationStore extends Store implements IProjectAspirationStore
{
  public function addAspirations(int $groupId, array $topicIds): bool
  {
    if ($this->isLocked($groupId)) {
      throw new \RuntimeException('Nguyện vọng đã được chốt, không thể thay đổi.');
    }

    try {
      $this->db->beginTransaction();

      // 1. Xóa tất cả các nguyện vọng cũ của nhóm
      $delQuery = (new QueryBuilder(new MySQLCompiler()))->from('project_aspirations')->delete()->eq('group_id', $groupId);
      $stmtDel = $this->db->prepare($delQuery->toSql());
      $stmtDel->execute($delQuery->getBindings());

      // 2. Thêm lại các nguyện vọng mới theo thứ tự
      if (!empty($topicIds)) {
        $rows = [];
        foreach ($topicIds as $index => $topicId) {
          $rows[] = [
            'group_id' => $groupId,
            'topic_id' => $topicId,
            'priority' => $index + 1,
            'status' => 'pending'
          ];
        }
        $insQuery = (new QueryBuilder(new MySQLCompiler()))->from('project_aspirations')->insert($rows);
        $stmtIns = $this->db->prepare($insQuery->toSql());
        $stmtIns->execute($insQuery->getBindings());
      }

      $this->db->commit();
      return true;
    } catch (\Throwable $th) {
      $this->db->rollBack();
      return false;
    }
  }

  public function getAspirationsByGroup(int $groupId): array
  {
    $sql = "SELECT a.*, tt.title as topic_title, tt.teacher_id, t.full_name as teacher_name
                FROM project_aspirations a
                JOIN project_topics tt ON a.topic_id = tt.id
                JOIN teachers t ON tt.teacher_id = t.id
                WHERE a.group_id = :group_id
                ORDER BY a.priority ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function updateAspirationStatus(int $id, string $status): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_aspirations')
      ->update(['status' => $status, 'updated_at' => AppTime::now()->format('Y-m-d H:i:s')])
      ->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function updateStatusByGroupAndTopic(int $groupId, int $topicId, string $status): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_aspirations')
      ->update(['status' => $status, 'updated_at' => AppTime::now()->format('Y-m-d H:i:s')])
      ->eq('group_id', $groupId)->eq('topic_id', $topicId);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function getAspirationsByBatch(int $batchId): array
  {
    $sql = "SELECT a.*, g.assigned_topic_id, g.created_at as group_created_at, tt.title as topic_title, a.locked_at
                FROM project_aspirations a
                JOIN project_groups g ON a.group_id = g.id
                LEFT JOIN project_topics tt ON a.topic_id = tt.id
                WHERE g.batch_id = :batch_id
                ORDER BY g.created_at ASC, a.group_id ASC, a.priority ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function lockAspirations(int $groupId): bool
  {
    $sql = "UPDATE project_aspirations SET locked_at = '" . AppTime::now()->format('Y-m-d H:i:s') . "' WHERE group_id = :group_id AND locked_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    return $stmt->rowCount() > 0;
  }

  public function unlockAspirations(int $groupId): bool
  {
    $sql = "UPDATE project_aspirations SET locked_at = NULL WHERE group_id = :group_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    return $stmt->rowCount() > 0;
  }

  public function isLocked(int $groupId): bool
  {
    $sql = "SELECT locked_at FROM project_aspirations WHERE group_id = :group_id AND locked_at IS NOT NULL LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    return (bool)$stmt->fetchColumn();
  }
}
