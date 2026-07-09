<?php

namespace App\Stores;

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use PDO;

interface IProjectGroupStore
{
  public function createGroup(int $batchId, int $leaderStudentId): int;
  public function addMember(int $groupId, int $studentId, bool $isLeader = false, bool $isConfirmed = true): bool;
  public function confirmMember(int $groupId, int $studentId): bool;
  public function removeMember(int $groupId, int $studentId): bool;
  public function deleteGroup(int $groupId): bool;
  public function getGroupById(int $id): ?array;
  public function getGroupByStudent(int $batchId, int $studentId): ?array;
  public function getGroupMembers(int $groupId): array;
  public function getPaginatedByBatch(int $batchId, int $page, int $limit = 15, array $filters = []): array;
  public function getTotalCountByBatch(int $batchId, array $filters = []): int;
  public function assignTopic(int $groupId, int $topicId): bool;
  public function getValidGroupsForAllocation(int $batchId): array;
  public function updateMemberEligibility(array $studentIds, int $isEligible): bool;
}

class ProjectGroupStore extends Store implements IProjectGroupStore
{
  public function createGroup(int $batchId, int $leaderStudentId): int
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_groups')->insert([
      'batch_id' => $batchId,
      'leader_student_id' => $leaderStudentId,
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int)$this->db->lastInsertId();
  }

  public function addMember(int $groupId, int $studentId, bool $isLeader = false, bool $isConfirmed = true): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_group_members')->insert([
      'group_id' => $groupId,
      'student_id' => $studentId,
      'is_leader' => $isLeader ? 1 : 0,
      'is_confirmed' => $isConfirmed ? 1 : 0,
      'is_eligible' => 1,
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function removeMember(int $groupId, int $studentId): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_group_members')->delete()
      ->eq('group_id', $groupId)->eq('student_id', $studentId);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function confirmMember(int $groupId, int $studentId): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_group_members')->update([
      'is_confirmed' => 1,
      'confirmed_at' => date('Y-m-d H:i:s')
    ])->eq('group_id', $groupId)->eq('student_id', $studentId);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function deleteGroup(int $groupId): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_groups')->delete()->eq('id', $groupId);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function getGroupById(int $id): ?array
  {
    $sql = "SELECT g.*, tt.title as assigned_topic_title, t.full_name as assigned_teacher_name
                FROM project_groups g
                LEFT JOIN project_topics tt ON g.assigned_topic_id = tt.id
                LEFT JOIN teachers t ON tt.teacher_id = t.id
                WHERE g.id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getGroupByStudent(int $batchId, int $studentId): ?array
  {
    $sql = "SELECT g.* 
                FROM project_groups g
                JOIN project_group_members gm ON g.id = gm.group_id
                WHERE g.batch_id = :batch_id AND gm.student_id = :student_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId, ':student_id' => $studentId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getGroupMembers(int $groupId): array
  {
    $sql = "SELECT gm.*, s.student_id as student_code, s.full_name, s.phone, c.short_name as classroom_name
                FROM project_group_members gm
                JOIN students s ON gm.student_id = s.id
                LEFT JOIN classrooms c ON s.classroom_id = c.id
                WHERE gm.group_id = :group_id
                ORDER BY gm.is_leader DESC, s.full_name ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getPaginatedByBatch(int $batchId, int $page, int $limit = 15, array $filters = []): array
  {
    $offset = ($page - 1) * $limit;
    $params = [':batch_id' => $batchId];
    $where = ["g.batch_id = :batch_id"];

    if (isset($filters['is_assigned'])) {
      if ($filters['is_assigned']) {
        $where[] = "g.assigned_topic_id IS NOT NULL";
      } else {
        $where[] = "g.assigned_topic_id IS NULL";
      }
    }

    $whereClause = implode(' AND ', $where);

    $sql = "SELECT g.*, tt.title as assigned_topic_title, t.full_name as assigned_teacher_name,
                       (SELECT COUNT(*) FROM project_group_members gm WHERE gm.group_id = g.id) as member_count
                FROM project_groups g
                LEFT JOIN project_topics tt ON g.assigned_topic_id = tt.id
                LEFT JOIN teachers t ON tt.teacher_id = t.id
                WHERE $whereClause
                ORDER BY g.created_at DESC 
                LIMIT $limit OFFSET $offset";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getTotalCountByBatch(int $batchId, array $filters = []): int
  {
    $params = [':batch_id' => $batchId];
    $where = ["g.batch_id = :batch_id"];

    if (isset($filters['is_assigned'])) {
      if ($filters['is_assigned']) {
        $where[] = "g.assigned_topic_id IS NOT NULL";
      } else {
        $where[] = "g.assigned_topic_id IS NULL";
      }
    }

    $whereClause = implode(' AND ', $where);

    $sql = "SELECT COUNT(*) FROM project_groups g WHERE $whereClause";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
  }

  public function assignTopic(int $groupId, int $topicId): bool
  {
    $sql = "UPDATE project_groups 
                SET assigned_topic_id = :topic_id, assigned_at = NOW(), updated_at = NOW() 
                WHERE id = :group_id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      ':group_id' => $groupId,
      ':topic_id' => $topicId
    ]);
  }

  public function getValidGroupsForAllocation(int $batchId): array
  {
    $sql = "SELECT g.* 
            FROM project_groups g
            WHERE g.batch_id = :batch_id
              AND g.assigned_topic_id IS NULL
              AND NOT EXISTS (
                  SELECT 1 FROM project_group_members gm 
                  WHERE gm.group_id = g.id AND (gm.is_confirmed = 0 OR gm.is_eligible = 0)
              )
              AND (SELECT COUNT(*) FROM project_group_members gm WHERE gm.group_id = g.id) = 
                  (SELECT COALESCE(max_students, 2) FROM project_batches WHERE id = g.batch_id)
            ORDER BY g.created_at ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function updateMemberEligibility(array $studentIds, int $isEligible): bool
  {
    if (empty($studentIds)) return false;

    $inPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));
    $sql = "UPDATE project_group_members SET is_eligible = ? WHERE student_id IN ($inPlaceholders)";

    $stmt = $this->db->prepare($sql);
    $params = array_merge([$isEligible], $studentIds);
    return $stmt->execute($params);
  }
}
