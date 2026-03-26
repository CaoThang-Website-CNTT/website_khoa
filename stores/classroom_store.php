<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/classroom.php';
require_once BASE_PATH . '/models/major.php';
require_once BASE_PATH . '/models/specialization.php';

use App\Core\Store;
use App\Models\{Classroom, Major, Specialization};
use PDO;

interface IClassroomStore
{
  /** @return Classroom[] */
  public function getAll(): array;
  /** @return Classroom[] */
  public function getPaginated(int $page, int $limit = 15): array;
  public function getById(int $id): ?Classroom;
  /** @return Classroom[] */
  public function getByIds(array $ids): array;
  public function create(Classroom $classroom): int;
  public function update(Classroom $classroom): bool;
  public function softDelete(int $id): bool;
  public function getTotalCount(): int;
  public function isShortNameUnique(string $shortName, ?int $excludeId = null): bool;

  // Majors and Specializations
  /** @return Major[] */
  public function getAllMajors(): array;
  public function getMajorById(int $id): ?Major;
  /** @return Major[] */
  public function getByMajorIds(array $majorIds): array;
  public function createMajor(Major $major): int;
  public function updateMajor(Major $major): bool;
  public function softDeleteMajor(int $id): bool;

  /** @return Specialization[] */
  public function getSpecializationsByMajorId(int $majorId): array;
  public function getSpecializationById(int $id): ?Specialization;
  /** @return Specialization[]*/
  public function getBySpecializationIds(array $specializationIds): array;
  public function createSpecialization(Specialization $specialization): int;
  public function updateSpecialization(Specialization $specialization): bool;
  public function softDeleteSpecialization(int $id): bool;
}

class ClassroomStore extends Store implements IClassroomStore
{
  /** @return Classroom[] */
  public function getAll(): array
  {
    $sql = "
      SELECT c.*, m.id AS maj_id, m.full_name AS maj_full_name, m.short_name AS maj_short_name, m.level AS maj_level, s.id AS spe_id, s.full_name AS spe_full_name, s.short_name AS spe_short_name
      FROM classrooms c
      LEFT JOIN majors m ON c.major_id = m.id
      LEFT JOIN specializations s ON c.specialization_id = s.id
      WHERE c.deleted_at IS NULL AND m.deleted_at IS NULL AND (s.deleted_at IS NULL OR s.id IS NULL)
      ORDER BY c.class_of DESC, c.letter DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();

    return array_map(fn($row) => Classroom::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /** @return Classroom[] */
  public function getPaginated(int $page, int $limit = 15): array
  {
    $offset = (max(1, $page) - 1) * $limit;

    $sql = "
      SELECT c.*, m.id AS maj_id, m.full_name AS maj_full_name, m.short_name AS maj_short_name, m.level AS maj_level, s.id AS spe_id, s.full_name AS spe_full_name, s.short_name AS spe_short_name
      FROM classrooms c
      LEFT JOIN majors m ON c.major_id = m.id
      LEFT JOIN specializations s ON c.specialization_id = s.id
      WHERE c.deleted_at IS NULL AND m.deleted_at IS NULL AND (s.deleted_at IS NULL OR s.id IS NULL)
      ORDER BY c.class_of DESC, c.letter DESC
      LIMIT :limit OFFSET :offset
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return array_map(fn($row) => Classroom::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getById(int $id): ?Classroom
  {
    $sql = "
      SELECT c.*, m.id AS maj_id, m.full_name AS maj_full_name, m.short_name AS maj_short_name, m.level AS maj_level, s.id AS spe_id, s.full_name AS spe_full_name, s.short_name AS spe_short_name
      FROM classrooms c
      LEFT JOIN majors m ON c.major_id = m.id
      LEFT JOIN specializations s ON c.specialization_id = s.id
      WHERE c.id = :id AND c.deleted_at IS NULL AND m.deleted_at IS NULL AND (s.deleted_at IS NULL OR s.id IS NULL)
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? Classroom::fromArray($row) : null;
  }

  /** @return Classroom[] */
  public function getByIds(array $ids): array
  {
    if (empty($ids)) {
      return [];
    }

    $placeholders = str_repeat('?,', count($ids) - 1) . '?';

    $sql = "
      SELECT c.*, m.id AS maj_id, m.full_name AS maj_full_name, m.short_name AS maj_short_name, m.level AS maj_level, s.id AS spe_id, s.full_name AS spe_full_name, s.short_name AS spe_short_name
      FROM classrooms c
      LEFT JOIN majors m ON c.major_id = m.id
      LEFT JOIN specializations s ON c.specialization_id = s.id
      WHERE c.id IN ($placeholders) AND c.deleted_at IS NULL AND m.deleted_at IS NULL AND (s.deleted_at IS NULL OR s.id IS NULL)
      ORDER BY c.class_of DESC, c.letter DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($ids);

    return array_map(fn($row) => Classroom::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function create(Classroom $classroom): int
  {
    $sql = "
      INSERT INTO classrooms (major_id, class_of, specialization_id, letter, short_name, created_at, updated_at)
      VALUES (:major_id, :class_of, :specialization_id, :letter, :short_name, NOW(), NOW())
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':major_id' => $classroom->major_id,
      ':class_of' => $classroom->class_of,
      ':specialization_id' => $classroom->specialization_id,
      ':letter' => $classroom->letter,
      ':short_name' => $classroom->short_name,
    ]);

    return (int) $this->db->lastInsertId();
  }

  public function update(Classroom $classroom): bool
  {
    $sql = "
      UPDATE classrooms SET
      major_id = :major_id,
      class_of = :class_of,
      specialization_id = :specialization_id,
      letter = :letter,
      short_name = :short_name,
      updated_at = NOW()
      WHERE id = :id AND deleted_at IS NULL
    ";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      ':major_id' => $classroom->major_id,
      ':class_of' => $classroom->class_of,
      ':specialization_id' => $classroom->specialization_id,
      ':letter' => $classroom->letter,
      ':short_name' => $classroom->short_name,
      ':id' => $classroom->id,
    ]);
  }

  public function softDelete(int $id): bool
  {
    $stmt = $this->db->prepare("
      UPDATE classrooms SET
      deleted_at = NOW() WHERE id = :id
    ");
    return $stmt->execute([':id' => $id]);
  }

  public function getTotalCount(): int
  {
    $stmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM classrooms
      WHERE deleted_at IS NULL
    ");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
  }

  public function isShortNameUnique(string $shortName, ?int $excludeId = null): bool
  {
    $sql = "
      SELECT COUNT(*)
      FROM classrooms
      WHERE short_name = :short_name AND deleted_at IS NULL
    ";
    $params = [':short_name' => $shortName];

    if ($excludeId !== null) {
      $sql .= " AND id != :exclude_id";
      $params[':exclude_id'] = $excludeId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
  }

  // Majors
  /** @return Major[] */
  public function getAllMajors(): array
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM majors
      WHERE deleted_at IS NULL ORDER BY level, full_name
    ");
    $stmt->execute();
    return array_map(fn($row) => Major::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getMajorById(int $id): ?Major
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM majors
      WHERE id = :id AND deleted_at IS NULL
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? Major::fromArray($row) : null;
  }
  /** @return Major[] */
  public function getByMajorIds(array $majorIds): array
  {
    if (empty($majorIds)) {
      return [];
    }

    $placeholders = str_repeat('?,', count($majorIds) - 1) . '?';

    $sql = "
      SELECT *
      FROM majors
      WHERE id IN ($placeholders) AND deleted_at IS NULL
      ORDER BY level, full_name
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($majorIds);

    return array_map(fn($row) => Major::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function createMajor(Major $major): int
  {
    $sql = "
      INSERT INTO majors (full_name, short_name, level, created_at, updated_at)
      VALUES (:full_name, :short_name, :level, NOW(), NOW())
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':full_name' => $major->full_name,
      ':short_name' => $major->short_name,
      ':level' => $major->level,
    ]);

    return (int) $this->db->lastInsertId();
  }

  public function updateMajor(Major $major): bool
  {
    $sql = "
      UPDATE majors SET
      full_name = :full_name,
      short_name = :short_name,
      level = :level,
      updated_at = NOW()
      WHERE id = :id AND deleted_at IS NULL
    ";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      ':full_name' => $major->full_name,
      ':short_name' => $major->short_name,
      ':level' => $major->level,
      ':id' => $major->id,
    ]);
  }

  public function softDeleteMajor(int $id): bool
  {
    $stmt = $this->db->prepare("
      UPDATE majors SET
      deleted_at = NOW() WHERE id = :id
    ");
    return $stmt->execute([':id' => $id]);
  }

  // Specializations
  /** @return Specialization[] */
  public function getSpecializationsByMajorId(int $majorId): array
  {
    $stmt = $this->db->prepare("
      SELECT * FROM specializations
      WHERE major_id = :major_id AND deleted_at IS NULL ORDER BY full_name
    ");
    $stmt->execute([':major_id' => $majorId]);
    return array_map(fn($row) => Specialization::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getSpecializationById(int $id): ?Specialization
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM specializations
      WHERE id = :id AND deleted_at IS NULL
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? Specialization::fromArray($row) : null;
  }

  /** @return Specialization[] */
  public function getBySpecializationIds(array $specializationIds): array
  {
    if (empty($specializationIds)) {
      return [];
    }

    $placeholders = str_repeat('?,', count($specializationIds) - 1) . '?';

    $sql = "
      SELECT *
      FROM specializations
      WHERE id IN ($placeholders) AND deleted_at IS NULL
      ORDER BY full_name
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($specializationIds);

    return array_map(fn($row) => Specialization::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function createSpecialization(Specialization $specialization): int
  {
    $sql = "
      INSERT INTO specializations (major_id, full_name, short_name, created_at, updated_at)
      VALUES (:major_id, :full_name, :short_name, NOW(), NOW())
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':major_id' => $specialization->major_id,
      ':full_name' => $specialization->full_name,
      ':short_name' => $specialization->short_name,
    ]);

    return (int) $this->db->lastInsertId();
  }

  public function updateSpecialization(Specialization $specialization): bool
  {
    $sql = "
      UPDATE specializations SET
      major_id = :major_id,
      full_name = :full_name,
      short_name = :short_name,
      updated_at = NOW()
      WHERE id = :id AND deleted_at IS NULL
    ";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      ':major_id' => $specialization->major_id,
      ':full_name' => $specialization->full_name,
      ':short_name' => $specialization->short_name,
      ':id' => $specialization->id,
    ]);
  }

  public function softDeleteSpecialization(int $id): bool
  {
    $stmt = $this->db->prepare("
      UPDATE specializations SET
      deleted_at = NOW() WHERE id = :id
    ");
    return $stmt->execute([':id' => $id]);
  }
}