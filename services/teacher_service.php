<?php

namespace App\Services;

require_once BASE_PATH . '/models/teacher.php';
require_once BASE_PATH . '/models/account.php';
require_once BASE_PATH . '/stores/teacher_store.php';
require_once BASE_PATH . '/stores/account_store.php';
require_once BASE_PATH . '/services/account_service.php';
require_once BASE_PATH . '/includes/core/pageable.php';

use App\Models\{Teacher};
use App\Stores\{TeacherStore, AccountStore};
use App\Core\Pageable;

interface ITeacherService
{
  /** @return Pageable */
  public function getAllTeachers(int $page, int $limit = 15): Pageable;

  public function getTotalTeachersCount(): int;

  public function isEmailUnique(string $email): bool;

  public function createTeacher(array $data, string $password): int;

  public function getTeacherById(int $id): ?Teacher;

  public function updateTeacher(int $id, array $data): bool;

  public function deleteTeacher(int $id): bool;
}

class TeacherService implements ITeacherService
{
  private TeacherStore $_teacherStore;
  private AccountStore $_accountStore;

  public function __construct(
    TeacherStore $teacherStore,
    AccountStore $accountStore,
  ) {
    $this->_teacherStore = $teacherStore;
    $this->_accountStore = $accountStore;
  }

  /** @return Pageable */
  public function getAllTeachers(int $page, int $limit = 15): Pageable
  {
    $teachers = $this->_teacherStore->getAll($page, $limit);
    $total = $this->_teacherStore->getTotalCount();
    return new Pageable($teachers, $total, $limit, $page);
  }

  public function getTotalTeachersCount(): int
  {
    return $this->_teacherStore->getTotalCount();
  }

  public function isEmailUnique(string $email): bool
  {
    return $this->_accountStore->isEmailUnique($email);
  }

  public function createTeacher(array $data, string $password): int
  {
    $accountId = $this->_accountStore->create($data['email'], $password, 'teacher');
    if (!$accountId) {
      throw new \Exception('Failed to create account');
    }

    $teacher = new Teacher();
    $teacher->account_id = $accountId;
    $teacher->staff_code = $data['staff_code'];
    $teacher->full_name = $data['full_name'];
    $teacher->gender = $data['gender'];
    $teacher->degree = $data['degree'] ?? null;
    $teacher->position = $data['position'] ?? null;
    $teacher->contract_type = $data['contract_type'] ?? 'full_time';

    return $this->_teacherStore->create($teacher);
  }

  public function getTeacherById(int $id): ?Teacher
  {
    $teacher = $this->_teacherStore->getById($id);
    if (!$teacher || !$teacher->account_id) {
      return $teacher;
    }

    $account = $this->_accountStore->getById($teacher->account_id);
    $teacher->account = $account;

    return $teacher;
  }

  public function updateTeacher(int $id, array $data): bool
  {
    $teacher = $this->_teacherStore->getById($id);
    if (!$teacher) {
      return false;
    }

    $teacher->full_name = $data['full_name'] ?? $teacher->full_name;
    $teacher->gender = $data['gender'] ?? $teacher->gender;
    $teacher->degree = $data['degree'] ?? $teacher->degree;
    $teacher->position = $data['position'] ?? $teacher->position;
    $teacher->contract_type = $data['contract_type'] ?? $teacher->contract_type;

    return $this->_teacherStore->update($teacher);
  }

  public function deleteTeacher(int $id): bool
  {
    return $this->_teacherStore->softDelete($id);
  }
}