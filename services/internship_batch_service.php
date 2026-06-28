<?php

namespace App\Services;

use App\Stores\{InternshipBatchStore, InternshipAssignmentStore, TeacherStore, AccountStore, InternshipSubmissionStore, ReferralLetterStore, StudentStore, ClassroomStore, InternshipGradeStore};
use App\Models\{Student, Classroom, InternshipBatch};
use App\Core\Pageable;
use App\Enums\BatchStatus;
use Database;
use Exception;

interface IInternshipBatchService
{
  public function createFullBatch(array $batchData, array $studentsInput, array $supervisors, int $adminId): int;
  public function getEligibleStudentsByClassroom(int $classroomId): array;
  public function getEligibleStudentsByClassrooms(array $classroomIds): array;
  public function validateStudentsBulk(array $studentIds): array;
  public function getActiveTeachers(): array;
  public function getAllClassrooms(): array;
  public function getBatches(int $page, int $limit = 15): Pageable;
  public function getBatchById(int $id): ?array;
  public function getBatchWithStats(int $id): ?array;
  public function updateBatch(int $id, array $data): bool;
  public function deleteBatch(int $id): bool;
  public function publishBatch(int $id): bool;
  public function closeBatch(int $id): bool;
  public function getBatchStudents(int $batchId): array;
  public function getBatchSupervisors(int $batchId): array;
  public function addStudentToBatch(int $batchId, int $studentId): bool;
  public function removeStudentFromBatch(int $batchId, int $studentId): bool;
  public function addSupervisorToBatch(int $batchId, int $teacherId, int $maxStudents): bool;
  public function removeSupervisorFromBatch(int $batchId, int $teacherId): bool;
  public function updateSupervisorQuota(int $batchId, int $teacherId, int $newQuota): bool;
  public function searchEligibleStudents(int $batchId, string $query = '', ?int $classroomId = null): array;
  public function searchEligibleTeachers(int $batchId, string $query = ''): array;
  public function getStudentDashboardData(int $studentId, ?int $batchId = null): array;
  public function updateStudentInternshipInfo(int $batchStudentId, array $data): bool;
  public function getTeacherBatchDetail(int $batchId, int $teacherId): ?array;
  public function isSupervisorOfBatch(int $batchId, int $teacherId): bool;
  public function getExportBatchStudents(int $batchId, array $filters = [], ?array $sort = null, array $selectedIds = []): array;
}

class InternshipBatchService implements IInternshipBatchService
{
  private InternshipBatchStore $_store;
  private InternshipAssignmentStore $_assignmentStore;
  private TeacherStore $_teacherStore;
  private AccountStore $_accountStore;
  private InternshipSubmissionStore $_submissionStore;
  private ReferralLetterStore $_referralLetterStore;
  private StudentStore $_studentStore;
  private ClassroomStore $_classroomStore;
  private InternshipGradeStore $_gradeStore;

  public function __construct(
    InternshipBatchStore $store,
    InternshipAssignmentStore $assignmentStore,
    TeacherStore $teacherStore,
    AccountStore $accountStore,
    InternshipSubmissionStore $submissionStore,
    ReferralLetterStore $referralLetterStore,
    StudentStore $studentStore,
    ClassroomStore $classroomStore,
    InternshipGradeStore $gradeStore
  ) {
    $this->_store = $store;
    $this->_assignmentStore = $assignmentStore;
    $this->_teacherStore = $teacherStore;
    $this->_accountStore = $accountStore;
    $this->_submissionStore = $submissionStore;
    $this->_referralLetterStore = $referralLetterStore;
    $this->_studentStore = $studentStore;
    $this->_classroomStore = $classroomStore;
    $this->_gradeStore = $gradeStore;
  }

  /**
   * Tạo toàn bộ đợt thực tập trong 1 transaction (Batch, Classrooms, Students, Supervisors)
   */
  public function createFullBatch(array $batchData, array $studentsInput, array $supervisors, int $adminId): int
  {
    return Database::getInstance()->transaction(function () use ($batchData, $studentsInput, $supervisors, $adminId) {
      $studentCodes = array_map(fn($sv) => trim($sv['student_code']), $studentsInput);
      if (!empty($studentCodes)) {
        $existingValidation = $this->_store->validateStudentsByStudentIds($studentCodes);
        $appEnv = $_ENV['APP_ENV'] ?? 'production';

        foreach ($existingValidation as $s) {
          if (!empty($s['batch_id']) && $appEnv !== 'local') {
            throw new Exception("Sinh viên {$s['student_id']} - {$s['full_name']} đã tham gia một đợt thực tập khác.");
          }
          if ($s['status'] !== 'Đang học') {
            throw new Exception("Sinh viên {$s['student_id']} - {$s['full_name']} có trạng thái không hợp lệ (Không phải 'Đang học').");
          }
        }
      }

      $missingClassrooms = [];
      foreach ($studentsInput as $sv) {
        $classroomShortName = trim($sv['classroom_name'] ?? '');
        if (!$this->_classroomStore->getByShortName($classroomShortName)) {
          $missingClassrooms[] = $classroomShortName;
        }
      }

      if (!empty($missingClassrooms)) {
        $missingClassrooms = array_unique($missingClassrooms);
        throw new Exception("Các lớp sau chưa tồn tại trong hệ thống: " . implode(', ', $missingClassrooms) . ". Vui lòng thêm các lớp này trước khi import.");
      }

      $batchData['created_by'] = $adminId;
      $batchId = $this->_store->createBatch($batchData);

      if (!$batchId) {
        throw new Exception('Không thể tạo thông tin đợt thực tập.');
      }

      $studentIds = [];
      $classroomIds = [];

      // Phân tích danh sách sinh viên
      foreach ($studentsInput as $sv) {
        // --- 1. XỬ LÝ CLASSROOM ---
        $classroomShortName = trim($sv['classroom_name'] ?? '');
        $classroom = $this->_classroomStore->getByShortName($classroomShortName);


        $classroomIds[] = $classroom->id;
        $assignedClassroomId = $classroom->id;

        // --- 2. XỬ LÝ ACCOUNT & STUDENT ---
        $studentCode = trim($sv['student_code']);
        $existingStudent = $this->_studentStore->getByStudentId($studentCode);

        if ($existingStudent) {
          $studentIds[] = $existingStudent->id;
        } else {
          $nationalId = trim($sv['national_id'] ?? '');
          if ($nationalId === '') {
            throw new Exception("Sinh viên $studentCode thiếu số CCCD.");
          }

          // Chưa có student => tạo Account, rồi tạo Student
          $email = $studentCode . '@caothang.edu.vn';
          // Mật khẩu mặc định là CCCD
          $account = $this->_accountStore->create($email, $nationalId, 'student');

          if (!$account) {
            throw new Exception("Tạo tài khoản thất bại cho sinh viên $studentCode.");
          }

          // Lấy thông tin ngành từ lớp nếu có
          $majorName = null;
          if ($classroom && $classroom->major_id) {
            $major = $this->_classroomStore->getMajorById($classroom->major_id);
            if ($major) {
              $majorName = $major->full_name;
            }
          }

          $newStudent = new Student(
            account_id: $account->id,
            student_id: $studentCode,
            full_name: $sv['full_name'],
            gender: 'male',
            dob: str_replace('/', '-', $sv['dob']),
            national_id: $nationalId,
            phone: '',
            address: '',
            classroom_id: $assignedClassroomId,
            birth_place: '',
            status: 'Đang học',
            major: $majorName
          );

          $newStudent = $this->_studentStore->create($newStudent);
          if (!$newStudent || !$newStudent->id) {
            throw new Exception("Tạo dữ liệu sinh viên thất bại cho $studentCode.");
          }
          $studentIds[] = $newStudent->id;
        }
      }

      // Xóa các ID trùng lặp trước khi thêm vào đợt thực tập
      $studentIds = array_unique($studentIds);
      $classroomIds = array_unique($classroomIds);

      $this->_store->addClassroomsToBatch($batchId, $classroomIds);
      $this->_store->addStudentsToBatch($batchId, $studentIds);
      $this->_store->addSupervisorsToBatch($batchId, $supervisors);

      return $batchId;
    });
  }

  public function getEligibleStudentsByClassroom(int $classroomId): array
  {
    return $this->_store->getEligibleStudentsByClassroom($classroomId);
  }

  public function getEligibleStudentsByClassrooms(array $classroomIds): array
  {
    return $this->_store->getEligibleStudentsByClassrooms($classroomIds);
  }

  public function validateStudentsBulk(array $studentIds): array
  {
    $students = $this->_store->validateStudentsByStudentIds($studentIds);

    $valid = [];
    $invalid = [];

    $studentsMap = [];
    foreach ($students as $s) {
      $studentsMap[$s['student_id']] = $s;
    }

    foreach ($studentIds as $studentId) {
      if (!isset($studentsMap[$studentId])) {
        $invalid[] = ['student_id' => $studentId, 'reason' => 'Không tìm thấy sinh viên trong hệ thống.'];
        continue;
      }

      $s = $studentsMap[$studentId];

      if ($s['status'] !== 'Đang học') {
        $invalid[] = ['student_id' => $studentId, 'reason' => 'Trạng thái không phải "Đang học".'];
        continue;
      }

      if ($s['batch_id']) {
        $invalid[] = ['student_id' => $studentId, 'reason' => 'Đã tham gia một đợt thực tập khác.'];
        continue;
      }

      $valid[] = $s;
    }

    return [
      'valid' => $valid,
      'invalid' => $invalid
    ];
  }

  public function getActiveTeachers(): array
  {
    return $this->_store->getActiveTeachers();
  }

  public function getAllClassrooms(): array
  {
    return $this->_store->getAllClassrooms();
  }

  public function getBatches(int $page, int $limit = 15): Pageable
  {
    $items = $this->_store->getPaginated($page, $limit);
    $total = $this->_store->getTotalCount();

    return new Pageable($items, $total, $limit, $page);
  }

  public function getBatchesByTeacherId(int $teacherId, int $page, int $limit = 15): Pageable
  {
    $items = $this->_store->getPaginatedByTeacherId($teacherId, $page, $limit);
    $total = $this->_store->getTotalCountByTeacherId($teacherId);

    return new Pageable($items, $total, $limit, $page);
  }

  public function getBatchById(int $id): ?array
  {
    return $this->_store->getById($id);
  }

  public function getBatchWithStats(int $id): ?array
  {
    $batch = $this->_store->getById($id);
    if (!$batch) return null;

    $stats = $this->_store->getBatchStats($id);
    return array_merge($batch, ['stats' => $stats]);
  }

  public function updateBatch(int $id, array $data): bool
  {
    return $this->_store->update($id, $data);
  }

  public function deleteBatch(int $id): bool
  {
    $stats = $this->_store->getBatchStats($id);

    if ($stats['has_submissions'] || $stats['has_grades']) {
      throw new Exception('Không thể xóa đợt thực tập đã có bài nộp hoặc điểm số.');
    }

    return $this->_store->delete($id);
  }

  public function publishBatch(int $id): bool
  {
    return $this->_store->updateStatus($id, BatchStatus::PUBLISHED, [
      'published_at' => date('Y-m-d H:i:s')
    ]);
  }

  public function closeBatch(int $id): bool
  {
    return $this->_store->updateStatus($id, BatchStatus::CLOSED, [
      'closed_at' => date('Y-m-d H:i:s')
    ]);
  }

  public function getBatchStudents(int $batchId): array
  {
    return $this->_store->getBatchStudentsWithDetails($batchId);
  }

  public function getBatchSupervisors(int $batchId): array
  {
    return $this->_store->getBatchSupervisorsWithDetails($batchId);
  }

  public function addStudentToBatch(int $batchId, int $studentId): bool
  {
    $this->checkBatchModifiable($batchId, 'sinh viên');
    return $this->_store->addStudentsToBatch($batchId, [$studentId]);
  }

  public function removeStudentFromBatch(int $batchId, int $studentId): bool
  {
    $this->checkBatchModifiable($batchId, 'sinh viên');
    return $this->_store->removeStudentFromBatch($batchId, $studentId);
  }

  private function checkBatchModifiable(int $batchId, string $actionType = 'giảng viên'): void
  {
    $batch = $this->_store->getById($batchId);
    if (!$batch) {
      throw new Exception('Đợt thực tập không tồn tại.');
    }

    $batchModel = new InternshipBatch();
    $batchModel->status = $batch['status'] ?? BatchStatus::DRAFT;
    $batchModel->start_at = $batch['start_at'] ?? null;
    $batchModel->end_at = $batch['end_at'] ?? null;

    if (in_array($batchModel->getEffectiveStatus(), [BatchStatus::CLOSED, BatchStatus::ENDED])) {
      throw new Exception("Không thể thay đổi thông tin $actionType khi đợt thực tập đã kết thúc.");
    }
  }

  public function addSupervisorToBatch(int $batchId, int $teacherId, int $maxStudents): bool
  {
    $this->checkBatchModifiable($batchId);
    if ($this->_store->isSupervisorOfBatch($batchId, $teacherId)) {
      throw new Exception('Giảng viên đã có trong danh sách quản lý của đợt này.');
    }
    return $this->_store->addSupervisorsToBatch($batchId, [
      ['teacher_id' => $teacherId, 'max_students' => $maxStudents]
    ]);
  }

  public function addSupervisorsBulk(int $batchId, array $supervisors): bool
  {
    $this->checkBatchModifiable($batchId);
    $validSupervisors = [];
    foreach ($supervisors as $sup) {
      if (!$this->_store->isSupervisorOfBatch($batchId, $sup['teacher_id'])) {
        $validSupervisors[] = $sup;
      }
    }
    if (empty($validSupervisors)) {
      return true;
    }
    return $this->_store->addSupervisorsToBatch($batchId, $validSupervisors);
  }

  public function removeSupervisorFromBatch(int $batchId, int $teacherId): bool
  {
    $this->checkBatchModifiable($batchId);
    $supervisors = $this->_store->getBatchSupervisorsWithDetails($batchId);
    foreach ($supervisors as $sup) {
      if ($sup['teacher_id'] == $teacherId && $sup['assigned_count'] > 0) {
        throw new Exception('Không thể xóa giảng viên đang có sinh viên hướng dẫn. Vui lòng phân công lại sinh viên sang giảng viên khác trước khi xóa.');
      }
    }
    return $this->_store->removeSupervisorFromBatch($batchId, $teacherId);
  }

  public function updateSupervisorQuota(int $batchId, int $teacherId, int $newQuota): bool
  {
    $this->checkBatchModifiable($batchId);

    // Kiểm tra quota mới có nhỏ hơn số lượng đã phân công không
    $supervisors = $this->_store->getBatchSupervisorsWithDetails($batchId);
    foreach ($supervisors as $sup) {
      if ($sup['teacher_id'] == $teacherId) {
        if ($newQuota < $sup['assigned_count']) {
          throw new Exception("Không thể giảm định mức xuống thấp hơn số sinh viên hiện đang hướng dẫn ({$sup['assigned_count']}).");
        }
        break;
      }
    }

    return $this->_store->updateSupervisorQuota($batchId, $teacherId, $newQuota);
  }

  public function searchEligibleStudents(int $batchId, string $query = '', ?int $classroomId = null): array
  {
    return $this->_store->searchEligibleStudents($batchId, $query, $classroomId);
  }

  public function searchEligibleTeachers(int $batchId, string $query = ''): array
  {
    return $this->_store->searchEligibleTeachers($batchId, $query);
  }

  public function getStudentDashboardData(int $studentId, ?int $batchId = null): array
  {
    // Lấy tất cả các đợt của SV này
    $batches = $this->_store->getBatchesByStudentId($studentId);

    if (empty($batches)) {
      return ['batches' => [], 'current' => null];
    }

    // Lấy đợt gần nhất mà SV tham gia
    $currentBatch = null;
    if ($batchId) {
      foreach ($batches as $b) {
        if ($b['id'] == $batchId) {
          $currentBatch = $b;
          break;
        }
      }
    } else {
      $currentBatch = $batches[0];
    }

    if (!$currentBatch) return ['batches' => $batches, 'current' => null];

    // Lấy chi tiết thông tin thực tập của SV
    $assignment = $this->_assignmentStore->getAssignmentByBatchStudentId($currentBatch['batch_student_id']);

    $supervisor = null;
    $logs = [];
    $submissions = [];
    $referralLetters = [];
    $grade = null;

    if ($assignment) {
      $supervisor = $this->_teacherStore->getById($assignment->teacher_id);
      if ($supervisor->account_id) {
        $supervisor->account = $this->_accountStore->getById($supervisor->account_id);
      }
      $logs = $this->_assignmentStore->getLogsByBatchStudent($currentBatch['batch_student_id']);
      $submissions = $this->_submissionStore->getAllByBatchStudentId($currentBatch['batch_student_id']);
      $referralLetters = $this->_referralLetterStore->getLettersWithCompanyByBatchStudentId($currentBatch['batch_student_id']);
      $grade = $this->_gradeStore->getByBatchStudentId($currentBatch['batch_student_id']);
    }

    return [
      'batches' => $batches,
      'current' => $currentBatch,
      'assignment' => $assignment,
      'supervisor' => $supervisor,
      'submissions' => $submissions,
      'logs' => $logs,
      'referralLetters' => $referralLetters,
      'grade' => $grade
    ];
  }

  public function updateStudentInternshipInfo(int $batchStudentId, array $data): bool
  {
    return $this->_store->updateBatchStudentCompany($batchStudentId, $data);
  }

  public function getTeacherBatchDetail(int $batchId, int $teacherId): ?array
  {
    $batch = $this->_store->getById($batchId);
    if (!$batch) return null;

    $stats = $this->_store->getTeacherBatchStats($batchId, $teacherId);
    $students = $this->_store->getTeacherStudentsInBatch($batchId, $teacherId);

    return [
      'batch' => $batch,
      'stats' => $stats,
      'students' => $students
    ];
  }

  public function isSupervisorOfBatch(int $batchId, int $teacherId): bool
  {
    return $this->_store->isSupervisorOfBatch($batchId, $teacherId);
  }

  public function getExportBatchStudents(int $batchId, array $filters = [], ?array $sort = null, array $selectedIds = []): array
  {
    return $this->_store->getExportBatchStudents($batchId, $filters, $sort, $selectedIds);
  }
}
