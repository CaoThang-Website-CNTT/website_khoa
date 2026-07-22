<?php

namespace App\Services;

use App\Core\AppTime;
use App\Core\Files\BatchStudentImporter;
use App\Stores\{InternshipBatchStore, InternshipAssignmentStore, TeacherStore, AccountStore, InternshipSubmissionStore, ReferralLetterStore, StudentStore, ClassroomStore, InternshipGradeStore};
use App\Models\Student;
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
  public function publishGrades(int $adminId, int $batchId): bool;
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
  public function getTeacherStudentDetail(int $batchStudentId): ?array;
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
    $this->validateBatchDates($batchData);
    return Database::getInstance()->transaction(function () use ($batchData, $studentsInput, $supervisors, $adminId) {
      $studentCodes = array_map(fn($sv) => trim($sv['student_code']), $studentsInput);
      if (!empty($studentCodes)) {
        $existingValidation = $this->_store->validateStudentsByStudentIds($studentCodes);
        foreach ($existingValidation as $s) {
          if ($s['status'] !== 'Đang học') {
            throw new Exception("Sinh viên {$s['student_id']} - {$s['full_name']} có trạng thái không hợp lệ (Không phải 'Đang học').");
          }
        }
      }

      $allClassrooms = $this->_classroomStore->getAll();
      $classroomMap = [];
      foreach ($allClassrooms as $c) {
        $normName = BatchStudentImporter::normalizeClassroomName($c->short_name);
        $classroomMap[$normName] = $c;
      }

      $missingClassrooms = [];
      foreach ($studentsInput as $studentInput) {
        $classroomShortName = trim($studentInput['classroom_name'] ?? '');
        $normName = BatchStudentImporter::normalizeClassroomName($classroomShortName);
        if ($classroomShortName === '' || !isset($classroomMap[$normName])) {
          $missingClassrooms[] = $classroomShortName !== '' ? $classroomShortName : '(trống)';
        }
      }

      if ($missingClassrooms !== []) {
        throw new Exception(
          'Các lớp sau chưa tồn tại trong hệ thống: ' . implode(', ', array_unique($missingClassrooms)) .
          '. Vui lòng thêm các lớp này trước khi import.'
        );
      }

      $batchData['created_by'] = $adminId;
      $batchId = $this->_store->createBatch($batchData);

      if (!$batchId) {
        throw new Exception('Không thể tạo thông tin đợt thực tập.');
      }

      $studentIds = [];
      $classroomIds = [];

      // Phân tích danh sách sinh viên: Tách nhóm hiện có và nhóm mới
      $allStudentCodes = array_map(fn($sv) => trim($sv['student_code']), $studentsInput);
      $existingStudents = $this->_studentStore->getByStudentIds($allStudentCodes);

      $existingStudentMap = [];
      foreach ($existingStudents as $es) {
        $existingStudentMap[$es->student_id] = $es;
      }

      $newStudentsInput = [];
      foreach ($studentsInput as $sv) {
        $studentCode = trim($sv['student_code']);

        // Xử lý lớp (dành cho việc gán vào đợt)
        $classroomShortName = trim($sv['classroom_name'] ?? '');
        $normName = BatchStudentImporter::normalizeClassroomName($classroomShortName);
        $classroom = $classroomMap[$normName] ?? null;

        if (!$classroom) {
          throw new Exception("Lớp $classroomShortName không còn tồn tại trong hệ thống.");
        }
        $classroomIds[] = $classroom->id;

        if (isset($existingStudentMap[$studentCode])) {
          $studentIds[] = $existingStudentMap[$studentCode]->id;
        } else {
          $newStudentsInput[] = ['sv' => $sv, 'classroom' => $classroom];
        }
      }

      // Xử lý nhóm sinh viên mới (Bulk Insert)
      if (!empty($newStudentsInput)) {
        $accountsData = [];
        $newEmails = [];
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        foreach ($newStudentsInput as $item) {
          $studentCode = trim($item['sv']['student_code']);
          $email = $studentCode . '@caothang.edu.vn';
          $newEmails[] = $email;

          // Dùng cost = 4 cho Bcrypt để tối ưu tốc độ khi import hàng loạt (< 1ms thay vì 100ms)
          $passwordHash = password_hash($studentCode, PASSWORD_BCRYPT, ['cost' => 4]);

          $accountsData[] = [
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => 'student',
            'created_at' => $now,
            'updated_at' => $now,
          ];
        }

        // Tạo tài khoản hàng loạt
        $this->_accountStore->createMany($accountsData);
        $newAccounts = $this->_accountStore->getByEmails($newEmails);

        $accountMap = [];
        foreach ($newAccounts as $acc) {
          $accountMap[$acc->email] = $acc;
        }

        // Tạo sinh viên hàng loạt
        $studentsData = [];
        $newStudentCodesForFetch = [];
        $majorMap = [];

        foreach ($newStudentsInput as $item) {
          $sv = $item['sv'];
          $classroom = $item['classroom'];

          $studentCode = trim($sv['student_code']);
          $email = $studentCode . '@caothang.edu.vn';

          if (!isset($accountMap[$email])) {
            throw new Exception("Tạo tài khoản thất bại cho sinh viên $studentCode.");
          }

          $accountId = $accountMap[$email]->id;

          $majorName = null;
          if ($classroom->major_id) {
            if (!isset($majorMap[$classroom->major_id])) {
              $major = $this->_classroomStore->getMajorById($classroom->major_id);
              $majorMap[$classroom->major_id] = $major?->full_name;
            }
            $majorName = $majorMap[$classroom->major_id];
          }

          $newStudentCodesForFetch[] = $studentCode;

          $studentsData[] = [
            'account_id' => $accountId,
            'student_id' => $studentCode,
            'full_name' => $sv['full_name'],
            'gender' => 'male',
            'dob' => str_replace('/', '-', $sv['dob']),
            'national_id' => $studentCode,
            'phone' => '',
            'address' => '',
            'classroom_id' => $classroom->id,
            'birth_place' => '',
            'notes' => null,
            'status' => 'Đang học',
            'major' => $majorName,
            'created_at' => $now,
            'updated_at' => $now,
          ];
        }

        $this->_studentStore->createMany($studentsData);
        $insertedStudents = $this->_studentStore->getByStudentIds($newStudentCodesForFetch);

        foreach ($insertedStudents as $ist) {
          $studentIds[] = $ist->id;
        }
      }

      // Xóa các ID trùng lặp trước khi thêm vào đợt thực tập
      $studentIds = array_unique($studentIds);
      $classroomIds = array_unique($classroomIds);

      // Kiểm tra Overlapping bằng 1 câu truy vấn
      if (($_ENV['APP_DEBUG'] ?? 'false') !== 'true' && !empty($studentIds)) {
        $overlappingIds = $this->_store->hasOverlappingEnrollments($studentIds, $batchData['start_at'], $batchData['end_at'], $batchId);
        if (!empty($overlappingIds)) {
          throw new Exception('Có ' . count($overlappingIds) . ' sinh viên đã được đăng ký vào một đợt thực tập khác có thời gian trùng lặp.');
        }
      }

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

      if (($_ENV['APP_DEBUG'] ?? 'false') !== 'true') {
        if ($s['batch_id']) {
          $invalid[] = ['student_id' => $studentId, 'reason' => 'Đã tham gia một đợt thực tập khác.'];
          continue;
        }
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
    if (!$batch)
      return null;

    $stats = $this->_store->getBatchStats($id);
    return array_merge($batch, ['stats' => $stats]);
  }

  public function updateBatch(int $id, array $data): bool
  {
    $this->checkBatchModifiable($id);
    $this->validateBatchDates($data);
    if (($_ENV['APP_DEBUG'] ?? 'false') !== 'true') {
      foreach ($this->_store->getBatchStudentsWithDetails($id) as $student) {
        if ($this->_store->hasOverlappingEnrollment((int) $student['student_id'], $data['start_at'], $data['end_at'], $id)) {
          throw new Exception("Không thể cập nhật ngày vì sinh viên {$student['student_code']} có một đợt thực tập khác bị trùng lịch.");
        }
      }
    }
    return $this->_store->update($id, $data);
  }

  public function deleteBatch(int $id): bool
  {
    $stats = $this->_store->getBatchStats($id);

    if ($stats['assigned_students'] > 0 || $stats['total_referrals'] > 0 || $stats['has_submissions'] || $stats['has_grades']) {
      throw new Exception('Không thể xóa đợt thực tập đã có bài nộp hoặc điểm số.');
    }

    return $this->_store->delete($id);
  }

  public function publishBatch(int $id): bool
  {
    $batch = $this->_store->getById($id);
    if (!$batch)
      throw new Exception('Không tìm thấy đợt thực tập.');
    if ($batch['status'] !== BatchStatus::DRAFT) {
      throw new Exception('Chỉ có đợt thực tập ở trạng thái bản nháp mới có thể công bố.');
    }
    $this->validateBatchDates($batch);
    if ($this->_store->getBatchStats($id)['total_students'] < 1) {
      throw new Exception('Vui lòng thêm ít nhất một sinh viên trước khi công bố.');
    }
    return $this->_store->updateStatus($id, BatchStatus::PUBLISHED, [
      'published_at' => AppTime::now()->format('Y-m-d H:i:s')
    ]);
  }

  public function publishGrades(int $adminId, int $batchId): bool
  {
    $batch = $this->_store->getById($batchId);
    if (!$batch)
      throw new Exception('Không tìm thấy đợt thực tập.');

    if (!in_array($batch['status'], [BatchStatus::PUBLISHED, BatchStatus::CLOSED])) {
      throw new Exception('Chỉ có đợt thực tập đang diễn ra hoặc đã kết thúc mới có thể công bố điểm.');
    }

    if ($this->_gradeStore->hasUnlockedDraftGrades($batchId)) {
      throw new Exception('Không thể công bố điểm vì vẫn còn điểm nháp chưa được chốt.');
    }

    return $this->_store->publishBatchGrades($batchId);
  }

  public function closeBatch(int $id): bool
  {
    $batch = $this->_store->getById($id);
    if (!$batch)
      throw new Exception('Không tìm thấy đợt thực tập.');
    if ($batch['status'] !== BatchStatus::PUBLISHED) {
      throw new Exception('Chỉ có đợt thực tập đã công bố mới có thể đóng.');
    }
    return $this->_store->updateStatus($id, BatchStatus::CLOSED, [
      'closed_at' => AppTime::now()->format('Y-m-d H:i:s')
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
    $this->checkBatchModifiable($batchId);
    $batch = $this->_store->getById($batchId);
    if ($this->_store->getBatchStudent($batchId, $studentId)) {
      throw new Exception('Sinh viên đã được đăng ký vào đợt thực tập này.');
    }
    if (($_ENV['APP_DEBUG'] ?? 'false') !== 'true') {
      if ($this->_store->hasOverlappingEnrollment($studentId, $batch['start_at'], $batch['end_at'], $batchId)) {
        throw new Exception('Sinh viên đã được đăng ký vào một đợt thực tập khác có thời gian trùng lặp.');
      }
    }
    return $this->_store->addStudentsToBatch($batchId, [$studentId]);
  }

  public function removeStudentFromBatch(int $batchId, int $studentId): bool
  {
    $this->checkBatchModifiable($batchId);
    $batchStudent = $this->_store->getBatchStudent($batchId, $studentId);
    if (!$batchStudent)
      throw new Exception('Sinh viên chưa được đăng ký vào đợt thực tập này.');
    if ($this->_store->batchStudentHasDependencies((int) $batchStudent['id'])) {
      throw new Exception('Không thể xóa sinh viên đã có phân công, giấy giới thiệu, báo cáo hoặc điểm số.');
    }
    return $this->_store->removeStudentFromBatch($batchId, $studentId);
  }

  private function checkBatchModifiable(int $batchId): void
  {
    $batch = $this->_store->getById($batchId);
    if (!$batch) {
      throw new Exception('Đợt thực tập không tồn tại.');
    }
    if ($batch['status'] === BatchStatus::CLOSED) {
      throw new Exception('Không thể thay đổi thông tin giảng viên khi đợt thực tập đã kết thúc.');
    }
  }

  private function validateBatchDates(array $data): void
  {
    if (empty($data['title']) || empty($data['start_at']) || empty($data['end_at'])) {
      throw new Exception('Tiêu đề, thời gian bắt đầu và thời gian kết thúc là bắt buộc.');
    }
    $start = strtotime((string) $data['start_at']);
    $end = strtotime((string) $data['end_at']);
    if ($start === false || $end === false || $start >= $end) {
      throw new Exception('Thời gian kết thúc phải lớn hơn thời gian bắt đầu.');
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

    if (!$currentBatch)
      return ['batches' => $batches, 'current' => null];

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
    if (!$batch)
      return null;

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

  public function getTeacherStudentDetail(int $batchStudentId): ?array
  {
    return $this->_store->getTeacherStudentDetail($batchStudentId);
  }

  public function getExportBatchStudents(int $batchId, array $filters = [], ?array $sort = null, array $selectedIds = []): array
  {
    return $this->_store->getExportBatchStudents($batchId, $filters, $sort, $selectedIds);
  }
}
