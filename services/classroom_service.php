<?php
namespace App\Services;

require_once BASE_PATH . '/models/major.php';
require_once BASE_PATH . '/models/specialization.php';
require_once BASE_PATH . '/models/classroom.php';
require_once BASE_PATH . '/db/database.php';

use App\Models\{Major, Specialization, Classroom};
use Database;
use PDO;

interface IClassroomRepository
{
  /** @return Classroom[] */
  public function getAllClassrooms(): array;
  public function getClassrooms(int $page, int $limit = 15): array;
  public function getClassroomById(int $id): ?Classroom;
  public function createClassroom(array $classroom): int;
  public function deleteClassroom(int $id): bool;

  /** @return Major[] */
  public function getAllMajors(): array;
  public function getSpecializationsByMajorId(int $id): array;
  public function getMajorsByLevel(string $level): array;
  public function createMajor(array $data): int;
  public function createSpecialization(array $data): int;
  public function getMajorById(int $id): ?array;
  public function getSpecializationById(int $id): ?array;
  public function updateClassroomInfo(int $id, array $data): bool;
  public function updateMajorFullName(int $id, string $fullName): bool;
  public function updateSpecializationFullName(int $id, string $fullName): bool;
  public function isClassroomShortNameUnique(string $name, ?int $excludeClassroomId = null): bool;
}
class ClassroomService implements IClassroomRepository
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }
  public function isClassroomShortNameUnique(string $name, ?int $excludeClassroomId = null): bool
  {
    $sql = "SELECT COUNT(*) FROM classrooms WHERE short_name = :short_name AND deleted_at IS NULL";
    $params = [':short_name' => $name];

    if ($excludeClassroomId) {
      $sql .= " AND id != :exclude_id";
      $params[':exclude_id'] = $excludeClassroomId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
  }

  public function getAllClassrooms(): array
  {
    $stmt = $this->db->prepare("SELECT * FROM `classrooms`");
    $stmt->execute();

    return array_map(fn($row) => Classroom::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getClassrooms(int $page, int $limit = 15): array
  {
    $offset = (max(1, $page) - 1) * $limit;

    $countSql = "SELECT COUNT(*) FROM classrooms c 
                LEFT JOIN majors m ON c.major_id = m.id 
                LEFT JOIN specializations s ON c.specialization_id = s.id 
                WHERE c.deleted_at IS NULL AND s.deleted_at IS NULL AND m.deleted_at IS NULL";
    $totalRows = $this->db->query($countSql)->fetchColumn();

    $sql = "SELECT 
              c.*,
              m.id AS maj_id, m.full_name as maj_full_name, m.short_name AS maj_short_name, m.level as maj_level,
              m.created_at AS maj_created_at, m.updated_at AS maj_updated_at, m.deleted_at AS maj_deleted_at, s.id AS spe_id, s.full_name as spe_full_name, s.short_name AS spe_short_name, 
              s.created_at AS spe_created_at, s.updated_at AS spe_updated_at, s.deleted_at AS spe_deleted_at
            FROM classrooms c 
            LEFT JOIN majors m ON c.major_id = m.id 
            LEFT JOIN specializations s ON c.specialization_id = s.id 
            WHERE c.deleted_at IS NULL AND s.deleted_at IS NULL AND m.deleted_at IS NULL
            ORDER BY c.class_of DESC, c.letter DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $items = array_map(fn($row) => Classroom::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    return [
      'data' => $items,
      'total_rows' => (int) $totalRows,
      'current_page' => $page,
      'last_page' => ceil($totalRows / $limit)
    ];
  }

  public function getClassroomById(int $id): Classroom
  {
    $sql = "SELECT  
              c.*,
              m.id AS maj_id, m.full_name as maj_full_name, m.short_name AS maj_short_name, m.level as maj_level, 
              m.created_at AS maj_created_at, m.updated_at AS maj_updated_at, m.deleted_at AS maj_deleted_at, s.id AS spe_id, s.full_name as spe_full_name, s.short_name AS spe_short_name, 
              s.created_at AS spe_created_at, s.updated_at AS spe_updated_at, s.deleted_at AS spe_deleted_at
            FROM classrooms c 
            LEFT JOIN majors m ON c.major_id = m.id 
            LEFT JOIN specializations s ON c.specialization_id = s.id 
            WHERE c.`id` = :id AND c.deleted_at IS NULL AND s.deleted_at IS NULL AND m.deleted_at IS NULL";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? Classroom::fromArray($row) : null;
  }

  public function createClassroom(array $classroom): int
  {
    $sql = "INSERT INTO `classrooms` (`major_id`, `class_of`, `specialization_id`, `letter`, `short_name`, `created_at`, `updated_at`) VALUES 
            (:major_id, :class_of, :specialization_id, :letter, :short_name, :created_at, :updated_at)";
    $stmt = $this->db->prepare($sql);
    $now = date('Y-m-d H:i:s');
    $stmt->execute([
      ':major_id' => $classroom['major_id'],
      ':class_of' => $classroom['class_of'],
      ':specialization_id' => isset($classroom['specialization_id']) ? $classroom['specialization_id'] : NULL,
      ':letter' => $classroom['letter'] ?? '',
      ':short_name' => $classroom['short_name'],
      ':created_at' => $now,
      ':updated_at' => $now
    ]);
    return (int) $this->db->lastInsertId();
  }

  public function deleteClassroom(int $id): bool
  {
    $sql = "UPDATE `classrooms` SET deleted_at = NOW() WHERE id = :id";
    return $this->db->prepare($sql)->execute([':id' => $id]);
  }

  public function getAllMajors(): array
  {
    $stmt = $this->db->prepare("SELECT * FROM `majors`
                                WHERE deleted_at IS NULL");
    $stmt->execute();

    return array_map(fn($row) => Major::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getSpecializationsByMajorId($id): array
  {
    $sql = "SELECT * FROM `specializations`
            WHERE major_id = :id AND deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return array_map(fn($row) => specialization::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getMajorsByLevel($level): array
  {
    $sql = "SELECT * FROM `majors`
            WHERE level LIKE :level AND deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':level', $level, PDO::PARAM_STR);
    $stmt->execute();
    return array_map(fn($row) => Major::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function createMajor(array $data): int
  {
    $sql = "INSERT INTO `majors` (`full_name`, `short_name`, `level`, `created_at`, `updated_at`) 
          VALUES (:full_name, :short_name, :level, NOW(), NOW())";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':full_name' => $data['full_name'],
      ':short_name' => $data['short_name'],
      ':level' => $data['level']
    ]);
    return (int) $this->db->lastInsertId();
  }

  public function createSpecialization(array $data): int
  {
    $sql = "INSERT INTO `specializations` (`major_id`, `full_name`, `short_name`, `created_at`, `updated_at`) 
          VALUES (:major_id, :full_name, :short_name, NOW(), NOW())";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':major_id' => $data['major_id'],
      ':full_name' => $data['full_name'],
      ':short_name' => $data['short_name']
    ]);
    return (int) $this->db->lastInsertId();
  }

  public function getMajorById(int $id): ?array
  {
    $stmt = $this->db->prepare("SELECT * FROM majors WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  public function getSpecializationById(int $id): ?array
  {
    $stmt = $this->db->prepare("SELECT * FROM specializations WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  public function updateClassroomInfo(int $id, array $data): bool
  {
    $sql = "UPDATE classrooms SET class_of = :class_of, letter = :letter, short_name = :short_name, updated_at = NOW() WHERE id = :id";
    return $this->db->prepare($sql)->execute([
      ':class_of' => $data['class_of'],
      ':letter' => $data['letter'] ?? '',
      ':short_name' => $data['short_name'],
      ':id' => $id
    ]);
  }

  public function updateMajorFullName(int $id, string $fullName): bool
  {
    return $this->db->prepare("UPDATE majors SET full_name = :full_name, updated_at = NOW() WHERE id = :id")
      ->execute([':full_name' => $fullName, ':id' => $id]);
  }

  public function updateSpecializationFullName(int $id, string $fullName): bool
  {
    return $this->db->prepare("UPDATE specializations SET full_name = :full_name, updated_at = NOW() WHERE id = :id")
      ->execute([':full_name' => $fullName, ':id' => $id]);
  }
}
?>