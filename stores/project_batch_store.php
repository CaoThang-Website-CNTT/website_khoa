<?php

namespace App\Stores;

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use PDO;

interface IProjectBatchStore
{
  public function createBatch(array $data): int;
  public function updateBatch(int $id, array $data): bool;
  public function updateStatus(int $id, string $status, array $extraData = []): bool;
  public function deleteBatch(int $id): bool;
  public function getById(int $id): ?array;
  public function getPaginated(int $page, int $limit = 15): array;
  public function getBatchesByTeacherId(int $teacherId, int $page, int $limit = 15): array;
  public function getTotalCount(): int;
  public function getBatchStats(int $id): array;
  public function getActiveBatches(): array;
  public function addSupervisors(int $batchId, array $supervisors): void;
  public function getSupervisorsByBatchId(int $batchId): array;
  public function isTeacherAssigned(int $batchId, int $teacherId): bool;
  public function getITTeachers(): array;
}

class ProjectBatchStore extends Store implements IProjectBatchStore
{
  public function createBatch(array $data): int
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_batches')->insert([
      'title' => $data['title'],
      'description' => $data['description'] ?? null,
      'topic_proposal_start' => $data['topic_proposal_start'] ?? null,
      'topic_proposal_end' => $data['topic_proposal_end'] ?? null,
      'registration_start' => $data['registration_start'] ?? null,
      'registration_end' => $data['registration_end'] ?? null,
      'max_aspirations' => $data['max_aspirations'] ?? 3,
      'min_class_of' => $data['min_class_of'] ?? 0,
      'max_class_of' => $data['max_class_of'] ?? 0,
      'status' => 'draft',
      'created_by' => $data['created_by'] ?? null,
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int)$this->db->lastInsertId();
  }

  public function updateBatch(int $id, array $data): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_batches')->update([
      'title' => $data['title'],
      'description' => $data['description'] ?? null,
      'topic_proposal_start' => $data['topic_proposal_start'] ?? null,
      'topic_proposal_end' => $data['topic_proposal_end'] ?? null,
      'registration_start' => $data['registration_start'] ?? null,
      'registration_end' => $data['registration_end'] ?? null,
      'max_aspirations' => $data['max_aspirations'] ?? 3,
      'min_class_of' => $data['min_class_of'] ?? 0,
      'max_class_of' => $data['max_class_of'] ?? 0,
      'updated_at' => date('Y-m-d H:i:s'),
    ])->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function updateStatus(int $id, string $status, array $extraData = []): bool
  {
    $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
    foreach (['published_at', 'closed_at'] as $key) {
      if (array_key_exists($key, $extraData)) {
        $data[$key] = $extraData[$key];
      }
    }
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_batches')->update($data)->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function deleteBatch(int $id): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_batches')->update([
      'deleted_at' => date('Y-m-d H:i:s'),
    ])->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function getById(int $id): ?array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_batches')
      ->select('*')->eq('id', $id)->is('deleted_at', null);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getPaginated(int $page, int $limit = 15): array
  {
    $offset = ($page - 1) * $limit;
    $sql = "SELECT * FROM project_batches 
                WHERE deleted_at IS NULL 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getBatchesByTeacherId(int $teacherId, int $page, int $limit = 15): array
  {
    $offset = ($page - 1) * $limit;
    $sql = "SELECT pb.* 
            FROM project_batches pb
            JOIN project_batch_supervisors pbs ON pb.id = pbs.batch_id
            WHERE pb.deleted_at IS NULL 
              AND pbs.teacher_id = :teacher_id 
              AND pbs.is_active = 1
            ORDER BY pb.created_at DESC 
            LIMIT :limit OFFSET :offset";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getTotalCount(): int
  {
    $sql = "SELECT COUNT(*) FROM project_batches WHERE deleted_at IS NULL";
    $stmt = $this->db->query($sql);
    return (int)$stmt->fetchColumn();
  }

  public function getTotalCountByTeacherId(int $teacherId): int
  {
    $sql = "SELECT COUNT(*) 
            FROM project_batches pb
            JOIN project_batch_supervisors pbs ON pb.id = pbs.batch_id
            WHERE pb.deleted_at IS NULL 
              AND pbs.teacher_id = :teacher_id 
              AND pbs.is_active = 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':teacher_id' => $teacherId]);
    return (int)$stmt->fetchColumn();
  }

  public function getBatchStats(int $id): array
  {
    $stats = [
      'total_topics' => 0,
      'approved_topics' => 0,
      'total_groups' => 0,
      'total_students' => 0,
    ];

    // Đếm đề tài
    $sqlTopics = "SELECT status, COUNT(*) as count FROM project_topics WHERE batch_id = :id AND deleted_at IS NULL GROUP BY status";
    $stmt = $this->db->prepare($sqlTopics);
    $stmt->execute([':id' => $id]);
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($topics as $row) {
      $stats['total_topics'] += $row['count'];
      if ($row['status'] === 'approved') {
        $stats['approved_topics'] += $row['count'];
      }
    }

    // Đếm nhóm
    $sqlGroups = "SELECT COUNT(*) FROM project_groups WHERE batch_id = :id";
    $stmt = $this->db->prepare($sqlGroups);
    $stmt->execute([':id' => $id]);
    $stats['total_groups'] = (int)$stmt->fetchColumn();

    // Đếm sinh viên tham gia
    $sqlStudents = "SELECT COUNT(*) FROM project_group_members gm 
                        JOIN project_groups g ON gm.group_id = g.id 
                        WHERE g.batch_id = :id AND gm.is_eligible = 1";
    $stmt = $this->db->prepare($sqlStudents);
    $stmt->execute([':id' => $id]);
    $stats['total_students'] = (int)$stmt->fetchColumn();

    // Đếm giảng viên tham gia
    $sqlSupervisors = "SELECT COUNT(*) FROM project_batch_supervisors WHERE batch_id = :id AND is_active = 1";
    $stmt = $this->db->prepare($sqlSupervisors);
    $stmt->execute([':id' => $id]);
    $stats['total_supervisors'] = (int)$stmt->fetchColumn();

    return $stats;
  }

  public function getActiveBatches(): array
  {
    $sql = "SELECT * FROM project_batches 
                WHERE deleted_at IS NULL AND status IN ('published', 'closed')
                ORDER BY created_at DESC";
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function addSupervisors(int $batchId, array $supervisors): void
  {
    if (empty($supervisors)) return;
    $rows = [];
    $now = date('Y-m-d H:i:s');
    foreach ($supervisors as $sv) {
      $rows[] = [
        'batch_id' => $batchId,
        'teacher_id' => $sv['teacher_id'],
        'min_students' => $sv['min_students'] ?? 0,
        'max_students' => $sv['max_students'] ?? 20,
        'is_active' => 1,
        'created_at' => $now,
        'updated_at' => $now
      ];
    }
    $query = (new QueryBuilder(new MySQLCompiler()))->from('project_batch_supervisors')->insert($rows);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
  }

  public function getSupervisorsByBatchId(int $batchId): array
  {
    $sql = "SELECT s.*, t.full_name, a.email 
            FROM project_batch_supervisors s
            JOIN teachers t ON s.teacher_id = t.id
            LEFT JOIN accounts a ON t.account_id = a.id
            WHERE s.batch_id = :batch_id AND s.is_active = 1
            ORDER BY t.full_name ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function isTeacherAssigned(int $batchId, int $teacherId): bool
  {
    $sql = "SELECT COUNT(*) FROM project_batch_supervisors
            WHERE batch_id = :batch_id AND teacher_id = :teacher_id AND is_active = 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId, ':teacher_id' => $teacherId]);
    return (int)$stmt->fetchColumn() > 0;
  }

  public function getITTeachers(): array
  {
    // Lấy giảng viên khoa CNTT (department_id = 1)
    $sql = "SELECT t.id as teacher_id, t.full_name, a.email 
            FROM teachers t
            JOIN accounts a ON t.account_id = a.id
            WHERE t.department_id = 1 AND a.deleted_at IS NULL
            ORDER BY t.full_name ASC";
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
