<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/teacher.php';

use App\Core\Store;
use App\Models\Teacher;
use PDO;

interface ITeacherStore
{
  /** @return Teacher[] */
  public function getAll(): array;
  /** @return Teacher[] */
  public function getPaginated(int $pageTo, int $limit = 15): array;
  public function getById(int $id): ?Teacher;
  /** @return Teacher[] */
  public function getByIds(array $ids): array;
  public function getByStaffCode(string $staffCode): ?Teacher;
  public function create(Teacher $teacher): Teacher;
  public function update(Teacher $teacher): bool;
  public function softDelete(int $id): bool;
  public function getTotalCount(): int;
}

class TeacherStore extends Store implements ITeacherStore
{
  /** @return Teacher[] */
  public function getAll(): array
  {
    $sql = "
      SELECT *
      FROM `teachers`
      WHERE `deleted_at` IS NULL
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();

    return array_map(fn($row) => Teacher::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  public function getPaginated(int $pageTo, int $limit = 15): array
  {
    $offset = (max(1, $pageTo) - 1) * $limit;

    $sql = "
      SELECT *
      FROM `teachers`
      WHERE deleted_at IS NULL
      LIMIT :limit OFFSET :offset
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return array_map(fn($row) => Teacher::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  public function getById(int $id): ?Teacher
  {
    $sql = "
      SELECT * FROM `teachers` 
      WHERE `id` = :id AND `deleted_at` IS NULL
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Teacher::fromArray($row) : null;
  }

  /** @return Teacher[] */
  public function getByIds(array $ids): array
  {
    if (empty($ids)) {
      return [];
    }

    $placeholders = str_repeat('?,', count($ids) - 1) . '?';

    $sql = "
      SELECT *
      FROM teachers
      WHERE id IN ($placeholders) AND deleted_at IS NULL
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($ids);

    return array_map(fn($row) => Teacher::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getByStaffCode(string $staffCode): ?Teacher
  {
    $sql = "
      SELECT * FROM `teachers` 
      WHERE `staff_code` = :staff_code AND `deleted_at` IS NULL
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['staff_code' => $staffCode]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Teacher::fromArray($row) : null;
  }

  public function create(Teacher $teacher): Teacher
  {
    $sql = "
      INSERT INTO `teachers` (
        `account_id`, `staff_code`, `full_name`, `gender`, `dob`, 
        `national_id`, `phone`, `address`, `degree`, `position`, 
        `title`, `department`, `contract_type`, `start_date`, 
        `end_date`, `notes`, `updated_at`
      ) VALUES (
        :acc_id, :staff_code, :full_name, :gender, :dob, 
        :national_id, :phone, :address, :degree, :position, 
        :title, :dept, :contract, :start_date, 
        :end_date, :notes, NOW()
      )
    ";

    $stmt = $this->db->prepare($sql);
    $success = $stmt->execute([
      ':acc_id' => $teacher->account_id,
      ':full_name' => $teacher->full_name,
      ':dob' => $teacher->dob,
      ':national_id' => $teacher->national_id,
      ':gender' => $teacher->gender,
      ':phone' => $teacher->phone,
      ':address' => $teacher->address,

      ':staff_code' => $teacher->staff_code,
      ':degree' => $teacher->degree,
      ':position' => $teacher->position,
      ':title' => $teacher->title,
      ':dept' => $teacher->department,
      ':contract' => $teacher->contract_type,
      ':start_date' => $teacher->start_date,
      ':end_date' => $teacher->end_date,
      ':notes' => $teacher->notes,
    ]);

    if (!$success) {
      throw new \Exception('Không thể lưu giảng viên vào cơ sở dữ liệu.');
    }

    $teacher->id = (int) $this->db->lastInsertId();

    return $teacher;
  }

  public function update(Teacher $teacher): bool
  {
    $sql = "
      UPDATE `teachers` SET 
        `staff_code`    = :staff_code,
        `full_name`     = :full_name,
        `gender`        = :gender,
        `dob`           = :dob,
        `national_id`   = :national_id,
        `phone`         = :phone,
        `address`       = :address,
        `degree`        = :degree,
        `position`      = :position,
        `title`         = :title,
        `department`    = :department,
        `contract_type` = :contract_type,
        `start_date`    = :start_date,
        `end_date`      = :end_date,
        `notes`         = :notes,
        `updated_at`    = NOW()
      WHERE `id` = :id AND `deleted_at` IS NULL
    ";

    return $this->db->prepare($sql)->execute([
      ':staff_code' => $teacher->staff_code,
      ':full_name' => $teacher->full_name,
      ':gender' => $teacher->gender,
      ':dob' => $teacher->dob,
      ':national_id' => $teacher->national_id,
      ':phone' => $teacher->phone,
      ':address' => $teacher->address,
      ':degree' => $teacher->degree,
      ':position' => $teacher->position,
      ':title' => $teacher->title,
      ':dept' => $teacher->department,
      ':contract' => $teacher->contract_type,
      ':start_date' => $teacher->start_date,
      ':end_date' => $teacher->end_date,
      ':notes' => $teacher->notes,
      ':id' => $teacher->id,
    ]);
  }

  public function softDelete(int $id): bool
  {
    $sql = "
      UPDATE `teachers` SET
      `deleted_at` = NOW() 
      WHERE `id` = :id
    ";

    return $this->db->prepare($sql)->execute([':id' => $id]);
  }

  public function getTotalCount(): int
  {
    $sql = "
      SELECT COUNT(id)
      FROM `teachers`
      WHERE `deleted_at` IS NULL
    ";

    $stmt = $this->db->query($sql);
    return (int) $stmt->fetchColumn();
  }
}