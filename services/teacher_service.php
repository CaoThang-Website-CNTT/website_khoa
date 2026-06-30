<?php

namespace App\Services;

require_once BASE_PATH . '/models/teacher.php';
require_once BASE_PATH . '/models/account.php';
require_once BASE_PATH . '/stores/teacher_store.php';
require_once BASE_PATH . '/stores/account_store.php';
require_once BASE_PATH . '/services/account_service.php';
require_once BASE_PATH . '/includes/core/pageable.php';

use App\Models\{Teacher};
use App\Stores\{TeacherStore, AccountStore, DepartmentStore};
use App\Models\{Department};
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
  /** @return Department[] */
  public function getAllDepartments(): array;
}

class TeacherService implements ITeacherService
{
  private TeacherStore $_teacherStore;
  private AccountStore $_accountStore;
  private DepartmentStore $_departmentStore;

  public function __construct(
    TeacherStore $teacherStore,
    AccountStore $accountStore,
    DepartmentStore $departmentStore
  ) {
    $this->_teacherStore = $teacherStore;
    $this->_accountStore = $accountStore;
    $this->_departmentStore = $departmentStore;
  }
  /** @return Teacher[] */
  public function getAllTeachers(): array
  {
    $teachers = $this->_teacherStore->getAll();

    $departmentIds = array_filter(array_column($teachers, 'department_id'));
    $departments = $this->_departmentStore->getByIds($departmentIds);
    $departmentMap = array_column($departments, null, 'id');

    $accountIds = array_filter(array_column($teachers, 'account_id'));
    $accounts = $this->_accountStore->getByIds($accountIds);
    $accountMap = array_column($accounts, null, 'id');

    foreach ($teachers as $teacher) {
      if ($teacher->department_id && isset($departmentMap[$teacher->department_id])) {
        $teacher->department = $departmentMap[$teacher->department_id];
      }
      if ($teacher->account_id && isset($accountMap[$teacher->account_id])) {
        $teacher->account = $accountMap[$teacher->account_id];
      }
    }

    return $teachers;
  }
  public function getTeachers(int $page, int $limit = 15): Pageable
  {
    $teachers = $this->_teacherStore->getPaginated($page, $limit);

    $departmentIds = array_filter(array_column($teachers, 'department_id'));
    $departments = $this->_departmentStore->getByIds($departmentIds);
    $departmentMap = array_column($departments, null, 'id');

    foreach ($teachers as $teacher) {
      if ($teacher->department_id && isset($departmentMap[$teacher->department_id])) {
        $teacher->department = $departmentMap[$teacher->department_id];
      }
    }

    $total = $this->_teacherStore->getTotalCount();
    return new Pageable($teachers, $total, $limit, $page);
  }

  public function getTotalTeachersCount(): int
  {
    return $this->_teacherStore->getTotalCount();
  }

  /**
   * @return Department[]
   */
  public function getAllDepartments(): array
  {
    return $this->_departmentStore->getAll();
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
      $account = $this->_accountStore->create($data['email'], $data['national_id'], 'teacher');
      if (!$account) {
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
      //
      //   'degree' => ['required', 'max:255'],
      //   'title' => ['nullable', 'max:150'],
      //   'position' => ['required', 'max:255'],
      //   'department_id' => ['required', 'numeric'],
      //   'start_date' => ['required', 'date'],
      //   'end_date' => ['required', 'date'],
      //   'notes' => ['nullable'],
      // ];
      $teacher = new Teacher(
        account_id: $account->id,
        full_name: $data['full_name'],
        dob: $data['dob'],
        national_id: $data['national_id'],
        gender: $data['gender'],
        phone: $data['phone'],
        address: $data['address'],

        degree: $data['degree'],
        title: $data['title'] ?? null,
        position: $data['position'],
        department_id: $data['department_id'],
        // start_date: $data['start_date'] ?? null,
        // end_date: $data['end_date'] ?? null,
        notes: $data['notes'] ?? null,
      );

      return $this->_teacherStore->create($teacher);
    });
  }

  public function getTeacherById(int $id): ?Teacher
  {
    $teacher = $this->_teacherStore->getById($id);
    if (!$teacher) {
      return null;
    }

    if ($teacher->account_id) {
      $teacher->account = $this->_accountStore->getById($teacher->account_id);
    }
    if ($teacher->department_id) {
      $teacher->department = $this->_departmentStore->getById($teacher->department_id);
    }

    return $teacher;
  }

  public function getTeacherByAccountId(int $accountId): ?Teacher
  {
    $teacher = $this->_teacherStore->getByAccountId($accountId);
    if (!$teacher) {
      return null;
    }

    if ($teacher->account_id) {
      $teacher->account = $this->_accountStore->getById($teacher->account_id);
    }
    if ($teacher->department_id) {
      $teacher->department = $this->_departmentStore->getById($teacher->department_id);
    }

    return $teacher;
  }

  public function updateTeacher(int $id, array $data): bool
  {
    $teacher = $this->_teacherStore->getById($id);
    if (!$teacher) {
      throw new \Exception('Không tồn tại giảng viên này');
    }

    //   'full_name' => ['required', 'max:255'],
    //   'dob' => ['required', 'date'],
    //   'national_id' => ['required', 'size:12'],
    //   'gender' => ['required', 'in:male,female'],
    //   'phone' => ['required', 'phone', 'max:15'],
    //   'address' => ['required'],

    //   'degree' => ['required', 'max:255'],
    //   'title' => ['nullable', 'max:150'],
    //   'position' => ['required', 'max:255'],
    //   'department_id' => ['required', 'numeric'],
    //   'start_date' => ['required', 'date'],
    //   'end_date' => ['required', 'date'],
    //   'notes' => ['nullable'],
    // ];
    $teacher->full_name = $data['full_name'] ?? $teacher->full_name;
    $teacher->dob = $data['dob'] ?? $teacher->dob;
    $teacher->national_id = $data['national_id'] ?? $teacher->national_id;
    $teacher->gender = $data['gender'] ?? $teacher->gender;
    $teacher->phone = $data['phone'] ?? $teacher->phone;
    $teacher->address = $data['address'] ?? $teacher->address;

    $teacher->degree = $data['degree'] ?? $teacher->degree;
    $teacher->title = $data['title'] ?? $teacher->title;
    $teacher->position = $data['position'] ?? $teacher->position;
    $teacher->department_id = $data['department_id'] ?? $teacher->department_id;
    //$teacher->start_date = $data['start_date'] ?? $teacher->start_date;
    //$teacher->end_date = $data['end_date'] ?? $teacher->end_date;
    $teacher->notes = $data['notes'] ?? $teacher->notes;

    return $this->_teacherStore->update($teacher);
  }

  public function deleteTeacher(int $id): bool
  {
    return $this->_teacherStore->softDelete($id);
  }
}
