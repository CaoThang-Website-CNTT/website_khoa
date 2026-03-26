<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/student.php';

use App\Core\Store;
use App\Models\Student;
use PDO;

interface IStudentStore
{
  /** @return Student[] */
  public function getAll(): array;

  /** @return Student[] */
  public function getPaginated(int $pageTo, int $limit = 15): array;

  public function getById(int $id): ?Student;

  public function create(Student $student): int;

  public function update(Student $student): bool;

  public function softDelete(int $id): bool;

  public function getTotalCount(): int;

  public function isStudentIdUnique(string $studentId, ?int $excludeAccountId = null): bool;

}
class StudentStore extends Store implements IStudentStore
{
  /** @return Student[] */
  public function getAll(): array
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM `students`
      WHERE deleted_at IS NULL
    ");

    $stmt->execute();

    return array_map(fn($row) => Student::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  /** @return Student[] */
  public function getPaginated(int $pageTo, int $limit = 15): array
  {
    $offset = (max(1, $pageTo) - 1) * $limit;

    $sql = "
      SELECT *
      FROM `students`
      WHERE deleted_at IS NULL
      LIMIT :limit OFFSET :offset
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return array_map(fn($row) => Student::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getById(int $id): ?Student
  {
    $sql = "
      SELECT * 
      FROM `students`
      WHERE `student_id` = :id
      LIMIT 1
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Student::fromArray($row) : null;
  }

  public function create(Student $data): int
  {
    $sql = "
      INSERT INTO `students` 
      (account_id, student_id, full_name, gender, dob, phone, classroom_id, major, birth_place) 
      VALUES 
      (:acc_id, :student_id, :name, :gender, :dob, :phone, :classroom_id, :major, :birth_place)
    ";

    return $this->db->prepare($sql)->execute([
      ':acc_id' => $data->account_id,
      ':student_id' => $data->student_id,
      ':name' => $data->full_name,
      ':gender' => $data->gender,
      ':dob' => $data->dob,
      ':phone' => $data->phone,
      ':classroom_id' => $data->classroom_id ?? null,
      ':major' => $data->major ?? null,
      ':birth_place' => $data->birth_place ?? null,
    ]);
  }

  public function update(Student $data): bool
  {
    $sql = "
      UPDATE `students` SET 
        `student_id`   = :student_id,
        `full_name`    = :full_name,
        `gender`       = :gender,
        `dob`          = :dob,
        `phone`        = :phone,
        `classroom_id` = :classroom_id,
        `address`      = :address,
        `national_id`  = :national_id,
        `major`        = :major,
        `birth_place`  = :birth_place,
        `status`       = :status,
        `notes`        = :notes,
        `updated_at`   = NOW()
      WHERE `id` = :id
    ";

    return $this->db->prepare($sql)->execute([
      ':student_id' => $data->student_id,
      ':full_name' => $data->full_name,
      ':gender' => $data->gender,
      ':dob' => $data->dob,
      ':phone' => $data->phone,
      ':classroom_id' => $data->classroom_id ?? null,
      ':address' => $data->address ?? null,
      ':national_id' => $data->national_id ?? null,
      ':major' => $data->major ?? null,
      ':birth_place' => $data->birth_place ?? null,
      ':status' => $data->status ?? 'Đang học',
      ':notes' => $data->notes ?? null,
      ':id' => $data->id,
    ]);
  }

  public function softDelete(int $id): bool
  {
    $sql = "
      UPDATE `accounts` SET
      `deleted_at` = NOW(), 
      WHERE `student_id` = :id AND `deleted_at` IS NULL
    ";

    return $this->db->prepare($sql)->execute([':id' => $id]);
  }
  public function isStudentIdUnique(string $id, ?int $excludeId = null): bool
  {
    $sql = "
      SELECT COUNT(*)
      FROM `students`
      WHERE `student_id` = :id
      AND `delete_at` IS NULL
    ";
    $params = [':id' => $id];

    if ($excludeId) {
      $sql .= " AND student_id != :exclude_id";
      $params[':student_id'] = $excludeId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
  }

  public function getTotalCount(): int
  {
    $sql = "
      SELECT COUNT(*) 
      FROM `students`
      WHERE `deleted_at` IS NULL
    ";

    $stmt = $this->db->query($sql);
    return (int) $stmt->fetchColumn();
  }
}
?>