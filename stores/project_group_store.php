<?php

namespace App\Stores;

use App\Core\AppTime;

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use Exception;
use PDO;

interface IProjectGroupStore
{
  public function createGroup(int $batchId, int $leaderStudentId): int;
  public function addMember(int $groupId, int $studentId, bool $isLeader = false, bool $isConfirmed = true): bool;
  public function confirmMember(int $groupId, int $studentId): bool;
  public function removeMember(int $groupId, int $studentId): bool;
  public function deleteGroup(int $groupId): bool;
  public function getGroupById(int $id): ?array;
  public function replaceGroupMember(int $groupId, int $oldStudentId, int $newStudentId): bool;
  public function getEligibleUnregisteredStudents(int $batchId): array;
  public function saveEligibleStudents(int $batchId, array $studentIds): void;
  public function getCurrentStudentsInBatch(int $batchId): array;
  public function getGroupByStudent(int $batchId, int $studentId): ?array;
  public function getGroupMembers(int $groupId): array;
  public function getPaginatedByBatch(int $batchId, int $page, int $limit = 15, array $filters = []): array;
  public function getTotalCountByBatch(int $batchId, array $filters = []): int;
  public function getAllocationStats(int $batchId): array;
  public function assignTopic(int $groupId, int $topicId): bool;
  public function getValidGroupsForAllocation(int $batchId): array;
  public function updateMemberEligibility(array $studentIds, int $isEligible): bool;
  public function getAssignedGroupsByTeacher(int $batchId, int $teacherId): array;
  public function getAssignedGroupsForPrint(int $batchId, int $teacherId, array $groupIds): array;
  public function updateRegistrationForm(int $groupId, array $data): bool;
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
      'confirmed_at' => AppTime::now()->format('Y-m-d H:i:s')
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
    $sql = "SELECT gm.*, s.student_id as student_code, s.full_name, s.phone, c.short_name as classroom_name, a.email
                FROM project_group_members gm
                JOIN students s ON gm.student_id = s.id
                LEFT JOIN accounts a ON s.account_id = a.id
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
    $joins = "LEFT JOIN project_topics tt ON g.assigned_topic_id = tt.id
              LEFT JOIN teachers t ON tt.teacher_id = t.id";

    if (isset($filters['is_assigned'])) {
      if ($filters['is_assigned']) {
        $where[] = "g.assigned_topic_id IS NOT NULL";
      } else {
        $where[] = "g.assigned_topic_id IS NULL";
      }
    }

    if (isset($filters['status']) && $filters['status'] === 'invalid') {
      $where[] = "EXISTS (
          SELECT 1 FROM project_group_members gm 
          WHERE gm.group_id = g.id AND (gm.is_eligible = 0 OR gm.is_confirmed = 0)
      )";
    }

    if (!empty($filters['teacher_id'])) {
      $where[] = "tt.teacher_id = :teacher_id";
      $params[':teacher_id'] = $filters['teacher_id'];
    }

    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $where[] = "(g.id LIKE :search1 OR tt.title LIKE :search2 OR t.full_name LIKE :search3 OR EXISTS (
            SELECT 1 FROM project_group_members gm 
            JOIN students s ON gm.student_id = s.id 
            WHERE gm.group_id = g.id AND (s.full_name LIKE :search4 OR s.student_id LIKE :search5)
        ))";
      $searchTerm = "%{$search}%";
      $params[':search1'] = $searchTerm;
      $params[':search2'] = $searchTerm;
      $params[':search3'] = $searchTerm;
      $params[':search4'] = $searchTerm;
      $params[':search5'] = $searchTerm;
    }

    $whereClause = implode(' AND ', $where);

    $orderBy = "g.created_at DESC";
    if (!empty($filters['sort']['col'])) {
      $col = $filters['sort']['col'];
      $dir = strtoupper($filters['sort']['dir']) === 'ASC' ? 'ASC' : 'DESC';
      if ($col === 'id') {
        $orderBy = "g.id $dir";
      } elseif ($col === 'assigned_topic_title') {
        $orderBy = "tt.title $dir";
      }
    }

    $sql = "SELECT g.*, tt.title as assigned_topic_title, t.full_name as assigned_teacher_name,
                       (SELECT COUNT(*) FROM project_group_members gm WHERE gm.group_id = g.id) as member_count,
                       (SELECT pa.priority FROM project_aspirations pa WHERE pa.group_id = g.id AND pa.topic_id = g.assigned_topic_id LIMIT 1) as assigned_priority
                FROM project_groups g
                $joins
                WHERE $whereClause
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getTotalCountByBatch(int $batchId, array $filters = []): int
  {
    $params = [':batch_id' => $batchId];
    $where = ["g.batch_id = :batch_id"];
    $joins = "LEFT JOIN project_topics tt ON g.assigned_topic_id = tt.id
              LEFT JOIN teachers t ON tt.teacher_id = t.id";

    if (isset($filters['is_assigned'])) {
      if ($filters['is_assigned']) {
        $where[] = "g.assigned_topic_id IS NOT NULL";
      } else {
        $where[] = "g.assigned_topic_id IS NULL";
      }
    }

    if (isset($filters['status']) && $filters['status'] === 'invalid') {
      $where[] = "EXISTS (
          SELECT 1 FROM project_group_members gm 
          WHERE gm.group_id = g.id AND (gm.is_eligible = 0 OR gm.is_confirmed = 0)
      )";
    }

    if (!empty($filters['teacher_id'])) {
      $where[] = "tt.teacher_id = :teacher_id";
      $params[':teacher_id'] = $filters['teacher_id'];
    }

    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $where[] = "(g.id LIKE :search1 OR tt.title LIKE :search2 OR t.full_name LIKE :search3 OR EXISTS (
            SELECT 1 FROM project_group_members gm 
            JOIN students s ON gm.student_id = s.id 
            WHERE gm.group_id = g.id AND (s.full_name LIKE :search4 OR s.student_id LIKE :search5)
        ))";
      $searchTerm = "%{$search}%";
      $params[':search1'] = $searchTerm;
      $params[':search2'] = $searchTerm;
      $params[':search3'] = $searchTerm;
      $params[':search4'] = $searchTerm;
      $params[':search5'] = $searchTerm;
    }

    $whereClause = implode(' AND ', $where);

    $sql = "SELECT COUNT(*) FROM project_groups g $joins WHERE $whereClause";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
  }

  public function getAllocationStats(int $batchId): array
  {
    $sql = "SELECT 
              COUNT(*) as total,
              SUM(CASE WHEN assigned_topic_id IS NOT NULL THEN 1 ELSE 0 END) as assigned,
              SUM(CASE WHEN assigned_topic_id IS NULL THEN 1 ELSE 0 END) as unassigned
            FROM project_groups 
            WHERE batch_id = :batch_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
      'total' => (int)($result['total'] ?? 0),
      'assigned' => (int)($result['assigned'] ?? 0),
      'unassigned' => (int)($result['unassigned'] ?? 0),
    ];
  }

  public function assignTopic(int $groupId, int $topicId): bool
  {
    $sql = "UPDATE project_groups 
                SET assigned_topic_id = :topic_id, assigned_at = '" . AppTime::now()->format('Y-m-d H:i:s') . "', updated_at = '" . AppTime::now()->format('Y-m-d H:i:s') . "'
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
              AND (
                  (SELECT COUNT(*) FROM project_group_members gm WHERE gm.group_id = g.id) = 2
                  OR 
                  ( (SELECT COUNT(*) FROM project_group_members gm WHERE gm.group_id = g.id) = 1 AND g.is_admin_approved_solo = 1 )
              )
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

  // --- Exception Handling ---

  public function getGroupsWithIneligibleMembers(int $batchId): array
  {
    $sql = "SELECT DISTINCT g.* 
            FROM project_groups g
            JOIN project_group_members gm ON g.id = gm.group_id
            WHERE g.batch_id = :batch_id AND gm.is_eligible = 0";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function dissolveGroup(int $groupId): bool
  {
    $this->db->beginTransaction();
    try {
      $stmt = $this->db->prepare("DELETE FROM project_groups WHERE id = ?");
      $stmt->execute([$groupId]);
      $this->db->commit();
      return true;
    } catch (Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }

  public function bulkDissolveInvalidGroups(int $batchId): int
  {
    $this->db->beginTransaction();
    try {
      $sql = "SELECT g.id
              FROM project_groups g
              WHERE g.batch_id = :batch_id
                AND NOT EXISTS (
                  SELECT 1 FROM project_group_members gm 
                  WHERE gm.group_id = g.id AND gm.is_eligible = 1
                )";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([':batch_id' => $batchId]);
      $groupIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

      if (empty($groupIds)) {
        $this->db->commit();
        return 0;
      }

      $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
      $delStmt = $this->db->prepare("DELETE FROM project_groups WHERE id IN ($placeholders)");
      $delStmt->execute($groupIds);

      $this->db->commit();
      return count($groupIds);
    } catch (Exception $e) {
      $this->db->rollBack();
      return 0;
    }
  }

  public function updateSoloApproval(int $groupId, bool $isApproved): bool
  {
    $this->db->beginTransaction();
    try {
      $query = (new QueryBuilder(new MySQLCompiler()))->from('project_groups')->update([
        'is_admin_approved_solo' => $isApproved ? 1 : 0
      ])->eq('id', $groupId);
      $stmt = $this->db->prepare($query->toSql());
      $stmt->execute($query->getBindings());

      if ($isApproved) {
        $queryDel = (new QueryBuilder(new MySQLCompiler()))->from('project_group_members')->delete()
          ->eq('group_id', $groupId)->eq('is_eligible', 0);
        $stmtDel = $this->db->prepare($queryDel->toSql());
        $stmtDel->execute($queryDel->getBindings());
      }
      $this->db->commit();
      return true;
    } catch (Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }

  public function replaceGroupMember(int $groupId, int $oldStudentId, int $newStudentId): bool
  {
    $this->db->beginTransaction();
    try {
      $sqlCheck = "SELECT is_leader FROM project_group_members WHERE group_id = :group_id AND student_id = :student_id";
      $stmtCheck = $this->db->prepare($sqlCheck);
      $stmtCheck->execute([':group_id' => $groupId, ':student_id' => $oldStudentId]);
      $isLeader = $stmtCheck->fetchColumn();

      if ($isLeader === false) {
        $this->db->rollBack();
        return false;
      }

      $queryDel = (new QueryBuilder(new MySQLCompiler()))->from('project_group_members')->delete()
        ->eq('group_id', $groupId)->eq('student_id', $oldStudentId);
      $stmtDel = $this->db->prepare($queryDel->toSql());
      $stmtDel->execute($queryDel->getBindings());

      $this->addMember($groupId, $newStudentId, (bool)$isLeader, true);

      if ($isLeader) {
        $queryUpd = (new QueryBuilder(new MySQLCompiler()))->from('project_groups')->update([
          'leader_student_id' => $newStudentId
        ])->eq('id', $groupId);
        $stmtUpd = $this->db->prepare($queryUpd->toSql());
        $stmtUpd->execute($queryUpd->getBindings());
      }

      $this->db->commit();
      return true;
    } catch (Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }

  public function getEligibleUnregisteredStudents(int $batchId): array
  {
    $sql = "
      SELECT s.* 
      FROM project_batch_eligible_students e
      JOIN students s ON e.student_id = s.id
      WHERE e.batch_id = :batch_id
      AND s.id NOT IN (
        SELECT project_group_members.student_id
        FROM project_groups
        JOIN project_group_members ON project_groups.id = project_group_members.group_id
        WHERE project_groups.batch_id = :batch_id_sub
        AND project_group_members.is_eligible = 1
      )
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['batch_id' => $batchId, 'batch_id_sub' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getStudentsInOtherActiveBatches(int $currentBatchId, array $studentIds): array
  {
    if (empty($studentIds)) return [];

    $inClause = implode(',', array_fill(0, count($studentIds), '?'));
    $sql = "
      SELECT DISTINCT s.id as student_id, s.full_name, s.student_code
      FROM students s
      WHERE s.id IN ($inClause)
      AND (
        EXISTS (
          SELECT 1 FROM project_batch_eligible_students e
          JOIN project_batches b ON e.batch_id = b.id
          WHERE e.student_id = s.id AND b.id != ? AND b.status != 'closed'
        )
        OR EXISTS (
          SELECT 1 FROM project_group_members m
          JOIN project_groups g ON m.group_id = g.id
          JOIN project_batches b ON g.batch_id = b.id
          WHERE m.student_id = s.id AND b.id != ? AND b.status != 'closed'
        )
      )
    ";

    $params = array_merge($studentIds, [$currentBatchId, $currentBatchId]);
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  public function saveEligibleStudents(int $batchId, array $studentIds): void
  {
    if (empty($studentIds)) return;
    $this->db->beginTransaction();
    try {
      $stmtDel = $this->db->prepare("DELETE FROM project_batch_eligible_students WHERE batch_id = ?");
      $stmtDel->execute([$batchId]);
      $stmtIns = $this->db->prepare("INSERT IGNORE INTO project_batch_eligible_students (batch_id, student_id) VALUES (?, ?)");
      foreach ($studentIds as $sId) {
        $stmtIns->execute([$batchId, $sId]);
      }
      $this->db->commit();
    } catch (Exception $e) {
      $this->db->rollBack();
      throw $e;
    }
  }

  public function getCurrentStudentsInBatch(int $batchId): array
  {
    $sql = "SELECT s.* 
            FROM students s
            JOIN project_group_members gm ON s.id = gm.student_id
            JOIN project_groups g ON gm.group_id = g.id
            WHERE g.batch_id = :batch_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  public function getAssignedGroupsByTeacher(int $batchId, int $teacherId): array
  {
    $sql = "SELECT g.*, tt.title AS assigned_topic_title, tt.description AS topic_description,
                   COUNT(gm.id) AS member_count,
                   SUM(CASE WHEN gm.is_eligible = 0 THEN 1 ELSE 0 END) AS ineligible_count
            FROM project_groups g
            JOIN project_topics tt ON tt.id = g.assigned_topic_id
            JOIN project_group_members gm ON gm.group_id = g.id
            WHERE g.batch_id = :batch_id AND tt.teacher_id = :teacher_id
            GROUP BY g.id, tt.title, tt.description
            ORDER BY tt.title ASC, g.assigned_at ASC, g.id ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId, ':teacher_id' => $teacherId]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($groups as &$group) {
      $group['members'] = $this->getGroupMembers((int)$group['id']);
    }
    return $groups;
  }

  public function getAssignedGroupsForPrint(int $batchId, int $teacherId, array $groupIds): array
  {
    $groupIds = array_values(array_unique(array_filter(array_map('intval', $groupIds))));
    if (!$groupIds) return [];
    $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
    $sql = "SELECT g.*, tt.title AS assigned_topic_title, tt.description AS topic_description,
                   t.full_name AS teacher_name
            FROM project_groups g
            JOIN project_topics tt ON tt.id = g.assigned_topic_id
            JOIN teachers t ON t.id = tt.teacher_id
            WHERE g.batch_id = ? AND tt.teacher_id = ? AND g.id IN ($placeholders)
            ORDER BY tt.title ASC, g.id ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(array_merge([$batchId, $teacherId], $groupIds));
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($groups as &$group) {
      $group['members'] = $this->getGroupMembers((int)$group['id']);
    }
    return $groups;
  }

  public function updateRegistrationForm(int $groupId, array $data): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_groups')->update([
      'registration_requirements' => $data['registration_requirements'] ?: null,
      'supervisor_opinion' => $data['supervisor_opinion'] ?: null,
      'execution_start' => $data['execution_start'] ?: null,
      'execution_end' => $data['execution_end'] ?: null,
      'updated_at' => AppTime::now()->format('Y-m-d H:i:s'),
    ])->eq('id', $groupId);
    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }
}
