<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/student.php';

use App\Core\Store;
use App\Models\Student;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
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
  public function update(Student $student): Student;
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
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('students')
      ->select('*')
      ->is('deleted_at', null)
      ->range(($pageTo - 1) * $limit, $pageTo * $limit - 1);

    $stmt = $this->db->prepare($query->toSql());
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
    $builder = new QueryBuilder(new MySQLCompiler());

    $data = [
      'account_id' => $student->account_id,
      'student_id' => $student->student_id,
      'full_name' => $student->full_name,
      'gender' => $student->gender,
      'dob' => $student->dob,
      'national_id' => $student->national_id,
      'phone' => $student->phone,
      'address' => $student->address,
      'classroom_id' => $student->classroom_id,
      'birth_place' => $student->birth_place,
      'notes' => $student->notes,
      'status' => $student->status,
      'major' => $student->major,
    ];

    $query = $builder->from('students')->insert($data);

    $stmt = $this->db->prepare($query->toSql());
    $success = $stmt->execute($query->getBindings());

    if (!$success) {
      throw new \Exception('Không thể lưu sinh viên vào cơ sở dữ liệu.');
    }

    $student->id = (int) $this->db->lastInsertId();
    return $student;
  }

  public function update(Student $student): Student
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $fields = [
      'student_id' => $student->student_id,
      'full_name' => $student->full_name,
      'gender' => $student->gender,
      'dob' => $student->dob,
      'phone' => $student->phone,
      'classroom_id' => $student->classroom_id ?? null,
      'address' => $student->address ?? null,
      'national_id' => $student->national_id ?? null,
      'major' => $student->major ?? null,
      'birth_place' => $student->birth_place ?? null,
      'status' => $student->status ?? 'Đang học',
      'notes' => $student->notes ?? null,
      'updated_at' => date('Y-m-d H:i:s'),
    ];

    $query = $builder
      ->from('students')
      ->eq('id', $student->id)
      ->update($fields);

    $stmt = $this->db->prepare($query->toSql());
    $success = $stmt->execute($query->getBindings());

    if (!$success) {
      throw new \Exception('Không thể cập nhật sinh viên trong cơ sở dữ liệu.');
    }

    return $student;
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
      $params[':exclude_id'] = $excludeId;
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