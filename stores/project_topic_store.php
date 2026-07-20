<?php

namespace App\Stores;

use App\Core\AppTime;

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use PDO;

interface IProjectTopicStore
{
  public function createTopic(array $data): int;
  public function updateTopic(int $id, array $data): bool;
  public function deleteTopic(int $id): bool;
  public function updateStatus(int $id, string $status, array $extraData = [], ?string $expectedOldStatus = null): bool;
  public function getById(int $id): ?array;
  public function getPaginatedByBatch(int $batchId, int $page, int $limit = 15, array $filters = []): array;
  public function getTotalCountByBatch(int $batchId, array $filters = []): int;
  public function getPendingCountByBatch(int $batchId): int;
  public function getStatusCountsByBatch(int $batchId): array;
  public function getTopicsByTeacher(int $batchId, int $teacherId): array;
  public function getApprovedTopics(int $batchId): array;
}

class ProjectTopicStore extends Store implements IProjectTopicStore
{
  public function createTopic(array $data): int
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_topics')->insert([
      'batch_id' => $data['batch_id'],
      'teacher_id' => $data['teacher_id'],
      'title' => $data['title'],
      'description' => $data['description'] ?? null,
      'pdf_file_path' => $data['pdf_file_path'] ?? null,
      'max_students' => $data['max_students'] ?? 2,
      'status' => $data['status'] ?? 'draft',
      'submitted_at' => $data['submitted_at'] ?? null,
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int)$this->db->lastInsertId();
  }

  public function updateTopic(int $id, array $data): bool
  {
    $updateData = [
      'title' => $data['title'],
      'description' => $data['description'] ?? null,
      'max_students' => $data['max_students'] ?? 2,
      'updated_at' => AppTime::now()->format('Y-m-d H:i:s'),
    ];
    if (isset($data['pdf_file_path'])) {
      $updateData['pdf_file_path'] = $data['pdf_file_path'];
    }
    if (isset($data['status'])) {
      $updateData['status'] = $data['status'];
    }
    if (array_key_exists('submitted_at', $data)) {
      $updateData['submitted_at'] = $data['submitted_at'];
    }

    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_topics')->update($updateData)->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function deleteTopic(int $id): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_topics')->update([
      'deleted_at' => AppTime::now()->format('Y-m-d H:i:s'),
    ])->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function updateStatus(int $id, string $status, array $extraData = [], ?string $expectedOldStatus = null): bool
  {
    $data = ['status' => $status, 'updated_at' => AppTime::now()->format('Y-m-d H:i:s')];
    foreach (['reviewed_at', 'reviewed_by', 'reject_reason', 'submitted_at'] as $key) {
      if (array_key_exists($key, $extraData)) {
        $data[$key] = $extraData[$key];
      }
    }
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_topics')->update($data)->eq('id', $id);
    
    if ($expectedOldStatus !== null) {
      $query->eq('status', $expectedOldStatus);
    }
    
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function getById(int $id): ?array
  {
    $sql = "SELECT tt.*, t.full_name as teacher_name, t.phone as teacher_phone, a.email as teacher_email,
                       (SELECT COUNT(*) FROM project_groups g WHERE g.assigned_topic_id = tt.id) as assigned_groups_count,
                       (SELECT IFNULL(SUM((SELECT COUNT(*) FROM project_group_members gm WHERE gm.group_id = asp.group_id)), 0) 
                        FROM project_aspirations asp 
                        WHERE asp.topic_id = tt.id AND asp.priority = 1 AND asp.locked_at IS NOT NULL) as registered_students_count,
                       (SELECT COUNT(*) FROM project_aspirations asp WHERE asp.topic_id = tt.id AND asp.priority = 1 AND asp.locked_at IS NOT NULL) as groups_nv1_count,
                       (SELECT COUNT(DISTINCT asp.group_id) FROM project_aspirations asp WHERE asp.topic_id = tt.id AND asp.locked_at IS NOT NULL) as groups_all_nv_count
                FROM project_topics tt
                JOIN teachers t ON tt.teacher_id = t.id
                LEFT JOIN accounts a ON t.account_id = a.id
                WHERE tt.id = :id AND tt.deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getPaginatedByBatch(int $batchId, int $page, int $limit = 15, array $filters = []): array
  {
    $offset = ($page - 1) * $limit;
    $params = [':batch_id' => $batchId];
    $where = ["tt.batch_id = :batch_id", "tt.deleted_at IS NULL"];

    if (!empty($filters['status'])) {
      $where[] = "tt.status = :status";
      $params[':status'] = $filters['status'];
    }
    if (!empty($filters['search'])) {
      $where[] = "(tt.title LIKE :search OR t.full_name LIKE :search2)";
      $params[':search'] = '%' . $filters['search'] . '%';
      $params[':search2'] = '%' . $filters['search'] . '%';
    }
    if (!empty($filters['teacher_id'])) {
      $where[] = "tt.teacher_id = :teacher_id";
      $params[':teacher_id'] = $filters['teacher_id'];
    }

    $whereClause = implode(' AND ', $where);

    $sql = "SELECT tt.*, t.full_name as teacher_name, d.short_name as department_name,
                       (SELECT COUNT(*) FROM project_groups g WHERE g.assigned_topic_id = tt.id) as assigned_groups_count,
                       (SELECT IFNULL(SUM((SELECT COUNT(*) FROM project_group_members gm WHERE gm.group_id = a.group_id)), 0) 
                        FROM project_aspirations a 
                        WHERE a.topic_id = tt.id AND a.priority = 1 AND a.locked_at IS NOT NULL) as registered_students_count
                FROM project_topics tt
                JOIN teachers t ON tt.teacher_id = t.id
                LEFT JOIN departments d ON t.department_id = d.id
                WHERE $whereClause
                ORDER BY tt.created_at DESC 
                LIMIT $limit OFFSET $offset";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getTotalCountByBatch(int $batchId, array $filters = []): int
  {
    $params = [':batch_id' => $batchId];
    $where = ["tt.batch_id = :batch_id", "tt.deleted_at IS NULL"];

    if (!empty($filters['status'])) {
      $where[] = "tt.status = :status";
      $params[':status'] = $filters['status'];
    }
    if (!empty($filters['search'])) {
      $where[] = "(tt.title LIKE :search OR t.full_name LIKE :search2)";
      $params[':search'] = '%' . $filters['search'] . '%';
      $params[':search2'] = '%' . $filters['search'] . '%';
    }
    if (!empty($filters['teacher_id'])) {
      $where[] = "tt.teacher_id = :teacher_id";
      $params[':teacher_id'] = $filters['teacher_id'];
    }

    $whereClause = implode(' AND ', $where);

    $sql = "SELECT COUNT(*) 
                FROM project_topics tt
                JOIN teachers t ON tt.teacher_id = t.id
                WHERE $whereClause";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
  }

  public function getPendingCountByBatch(int $batchId): int
  {
    $sql = "SELECT COUNT(*) FROM project_topics WHERE batch_id = :batch_id AND status = 'pending' AND deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return (int)$stmt->fetchColumn();
  }

  public function getStatusCountsByBatch(int $batchId): array
  {
    $sql = "SELECT status, COUNT(*) AS total
            FROM project_topics
            WHERE batch_id = :batch_id AND deleted_at IS NULL
            GROUP BY status";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);

    $counts = ['all' => 0];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $count = (int) $row['total'];
      $counts[(string) $row['status']] = $count;
      $counts['all'] += $count;
    }
    return $counts;
  }

  public function getTopicsByTeacher(int $batchId, int $teacherId): array
  {
    $sql = "SELECT tt.*, 
                       (SELECT COUNT(*) FROM project_groups g WHERE g.assigned_topic_id = tt.id) as assigned_groups_count,
                       (SELECT IFNULL(SUM((SELECT COUNT(*) FROM project_group_members gm WHERE gm.group_id = a.group_id)), 0) 
                        FROM project_aspirations a 
                        WHERE a.topic_id = tt.id AND a.priority = 1 AND a.locked_at IS NOT NULL) as registered_students_nv1
                FROM project_topics tt
                WHERE tt.batch_id = :batch_id AND tt.teacher_id = :teacher_id AND tt.deleted_at IS NULL
                ORDER BY tt.created_at DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId, ':teacher_id' => $teacherId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getApprovedTopics(int $batchId): array
  {
    $sql = "SELECT tt.*, t.full_name as teacher_name, t.degree
                FROM project_topics tt
                JOIN teachers t ON tt.teacher_id = t.id
                WHERE tt.batch_id = :batch_id AND tt.status = 'approved' AND tt.deleted_at IS NULL
                ORDER BY t.full_name ASC, tt.title ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
