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
use Database;

interface ITeacherService
{
  /** @return Teacher[] */
  public function getAllTeachers(): array;
  /** @return Pageable */
  public function getTeachers(int $page, int $limit = 15): Pageable;
  public function getTotalTeachersCount(): int;
  public function isEmailUnique(string $email): bool;
  public function createTeacher(array $data): ?Teacher;
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
  /** @return Teacher[] */
  public function getAllTeachers(): array
  {
    return $this->_teacherStore->getAll();
  }
  /** @return Pageable */
  public function getTeachers(int $page, int $limit = 15): Pageable
  {
    $teachers = $this->_teacherStore->getPaginated($page, $limit);
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

  public function createTeacher(array $data): ?Teacher
  {
    return Database::getInstance()->transaction(function () use ($data) {
      // Check mail
      // Nếu email tồn tại, kiểm tra unique nó
      // Còn không thì tạo hovaten@caothang.edu.vn (no space, lowercase)
      if (isset($data['email'])) {
        if (!$this->_accountStore->isEmailUnique($data['email'])) {
          throw new \Exception('Email này đã tồn tại trong hệ thống.');
        }
      } else {
        $data['email'] = str_replace('-', '', generateSlug($data['full_name'])) . '@caothang.edu.vn';
      }

      // Create account
      $accountId = $this->_accountStore->create($data['email'], $data['national_id'], 'teacher');
      if (!$accountId) {
        throw new \Exception('Tạo tài khoản thất bại');
      }

      // Create teacher
      //       $rules = [
      //   'full_name' => ['required', 'max:255'],
      //   'dob' => ['required', 'date'],
      //   'national_id' => ['required', 'size:12'],
      //   'gender' => ['required', 'in:male,female'],
      //   'phone' => ['required', 'phone', 'max:15'],
      //   'address' => ['required'],

      //   'staff_code' => ['required', 'size:10'],
      //   'degree' => ['required', 'max:255'],
      //   'title' => ['nullable', 'max:150'],
      //   'position' => ['required', 'max:255'],
      //   'department' => ['required', 'max:255'],
      //   'contract_type' => ['required', 'in:full-time,part-time,visiting,contract'],
      //   'start_date' => ['required', 'date'],
      //   'end_date' => ['required', 'date'],
      //   'notes' => ['nullable'],
      // ];
      $teacher = new Teacher(
        account_id: $accountId,
        full_name: $data['full_name'],
        dob: $data['dob'],
        national_id: $data['national_id'],
        gender: $data['gender'],
        phone: $data['phone'],
        address: $data['address'],

        staff_code: $data['staff_code'],
        degree: $data['degree'],
        title: $data['title'] ?? null,
        position: $data['position'],
        department: $data['department'],
        contract_type: $data['contract_type'],
        start_date: $data['start_date'],
        end_date: $data['end_date'],
        notes: $data['notes'] ?? null,
      );

      return $this->_teacherStore->create($teacher);
    });
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