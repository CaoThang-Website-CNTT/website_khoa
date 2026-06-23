<?php

namespace App\Stores;

use App\Core\Schema\Compiler\MySQLCompiler;
use App\Core\Schema\QueryBuilder;
use App\Models\ReferralLetterStudent;
use App\Core\Store;
use PDO;

interface IReferralLetterStudentStore
{
  public function createBulk(int $letterId, array $students): bool;
  /** @return ReferralLetterStudent[] */
  public function getByLetterId(int $letterId): array;
  /** @return array Grouped by letter_id */
  public function getByLetterIds(array $letterIds): array;
  public function deleteByLetterId(int $letterId): bool;
}

class ReferralLetterStudentStore extends Store implements IReferralLetterStudentStore
{
  public function createBulk(int $letterId, array $students): bool
  {
    if (empty($students)) {
      return true;
    }

    $insertData = [];

    foreach ($students as $index => $student) {
      $insertData[] = [
        'referral_letter_id' => $letterId,
        'full_name' => $student['full_name'],
        'training_program' => $student['training_program'] ?? null,
        'dob' => $student['dob'] ?? null,
        'address' => $student['address'] ?? null,
        'student_id' => $student['student_id'] ?? null,
        'batch_student_id' => $student['batch_student_id'] ?? null,
        'sort_order' => $student['sort_order'] ?? $index
      ];
    }

    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('referral_letter_students')->insert($insertData);

    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }

  public function getByLetterId(int $letterId): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('referral_letter_students')
      ->select('*')
      ->eq('referral_letter_id', $letterId)
      ->order('sort_order', ['ascending' => true])
      ->order('id', ['ascending' => true]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $results = [];
    while ($row = $stmt->fetch()) {
      $results[] = new ReferralLetterStudent(
        id: (int)$row['id'],
        referral_letter_id: (int)$row['referral_letter_id'],
        full_name: $row['full_name'],
        training_program: $row['training_program'],
        dob: $row['dob'],
        address: $row['address'],
        student_id: $row['student_id'] ? (int)$row['student_id'] : null,
        batch_student_id: $row['batch_student_id'] ? (int)$row['batch_student_id'] : null,
        sort_order: (int)$row['sort_order'],
        created_at: $row['created_at'],
        updated_at: $row['updated_at']
      );
    }
    return $results;
  }

  public function getByLetterIds(array $letterIds): array
  {
    if (empty($letterIds)) return [];

    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('referral_letter_students')
      ->select('*')
      ->in('referral_letter_id', $letterIds)
      ->order('referral_letter_id', ['ascending' => true])
      ->order('sort_order', ['ascending' => true])
      ->order('id', ['ascending' => true]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $results = [];
    while ($row = $stmt->fetch()) {
      $letterId = (int)$row['referral_letter_id'];
      if (!isset($results[$letterId])) {
        $results[$letterId] = [];
      }
      $results[$letterId][] = clone new ReferralLetterStudent(
        id: (int)$row['id'],
        referral_letter_id: $letterId,
        full_name: $row['full_name'],
        training_program: $row['training_program'],
        dob: $row['dob'],
        address: $row['address'],
        student_id: $row['student_id'] ? (int)$row['student_id'] : null,
        batch_student_id: $row['batch_student_id'] ? (int)$row['batch_student_id'] : null,
        sort_order: (int)$row['sort_order'],
        created_at: $row['created_at'],
        updated_at: $row['updated_at']
      );
    }
    return $results;
  }

  public function deleteByLetterId(int $letterId): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('referral_letter_students')
      ->eq('referral_letter_id', $letterId)
      ->delete();

    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }
}
