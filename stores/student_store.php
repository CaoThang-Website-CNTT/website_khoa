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
  public function getByStudentId(int $student_id): ?Student;
  public function create(Student $student): Student;
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
      WHERE `id` = :id
      LIMIT 1
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Student::fromArray($row) : null;
  }
  public function getByStudentId($student_id): ?Student
  {
    $sql = "
      SELECT * 
      FROM `students`
      WHERE `student_id` = :id
      LIMIT 1
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $student_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Student::fromArray($row) : null;

  }
  public function create(Student $student): Student
  {
    $sql = "
      INSERT INTO `students` 
      (
        account_id, student_id, full_name, gender, dob, 
        national_id, phone, address, classroom_id, 
        birth_place, notes, status, major
      ) 
      VALUES 
      (
        :acc_id, :student_id, :name, :gender, :dob, 
        :nat_id, :phone, :address, :class_id, 
        :birth_place, :notes, :status, :major
      )
    ";

    $stmt = $this->db->prepare($sql);
    $success = $stmt->execute([
      ':acc_id' => $student->account_id,
      ':student_id' => $student->student_id,
      ':name' => $student->full_name,
      ':gender' => $student->gender,
      ':dob' => $student->dob,
      ':nat_id' => $student->national_id,
      ':phone' => $student->phone,
      ':address' => $student->address,
      ':class_id' => $student->classroom_id,
      ':birth_place' => $student->birth_place,
      ':notes' => $student->notes,
      ':status' => $student->status,

      ':major' => $student->major,
    ]);

    if (!$success) {
      throw new \Exception('Không thể lưu sinh viên vào cơ sở dữ liệu.');
    }

    $student->id = (int) $this->db->lastInsertId();

    return $student;
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

    $stmt = $this->db->prepare($sql);
    $success = $stmt->execute([
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

    if (!$success) {
      throw new \Exception('Không thể lưu sinh viên vào cơ sở dữ liệu.');
    }

    return $success;
  }

  public function softDelete($student_id): bool
  {
    $sql = "
      UPDATE `accounts`
      INNER JOIN `students` ON `accounts`.`id` = `students`.`account_id`
      SET
        `accounts`.`deleted_at` = NOW(),
        `students`.`deleted_at` = NOW(),
        `students`.`status` = N'Thôi học'
      WHERE `students`.`student_id` = :id;
    ";
    $stmt = $this->db->prepare($sql);

    $stmt->execute([':id' => $student_id]);

    if ($stmt->rowCount() === 0) {
      throw new \Exception("Không tìm thấy sinh viên mã: " . $student_id);
    }

    return true;
  }
  public function isStudentIdUnique(string $id, ?int $excludeId = null): bool
  {
    $sql = "
      SELECT COUNT(*)
      FROM `students`
      WHERE `student_id` = :id
      AND `deleted_at` IS NULL
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