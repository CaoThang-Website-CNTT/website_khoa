<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Controller;
use App\Services\InternshipBatchService;
use App\Services\ReferralLetterService;
use App\Services\InternshipGradeService;
use Exception;

class InternshipBatchManagementApiController extends Controller
{
  private InternshipBatchService $_batchService;
  private ReferralLetterService $_referralLetterService;
  private InternshipGradeService $_gradeService;

  public function __construct(InternshipBatchService $batchService, ReferralLetterService $referralLetterService, InternshipGradeService $gradeService)
  {
    $this->_batchService = $batchService;
    $this->_referralLetterService = $referralLetterService;
    $this->_gradeService = $gradeService;
  }

  /**
   * Lấy danh sách sinh viên trong đợt
   */
  public function getStudents($id)
  {
    try {
      $students = $this->_batchService->getBatchStudents((int) $id);
      return $this->json($students, 200);
    } catch (Exception $e) {
      return $this->json(null, 500, $e->getMessage());
    }
  }

  /**
   * Thêm sinh viên vào đợt
   */
  public function addStudent($id, Request $request)
  {
    $data = $request->json();
    $studentId = $data['student_id'] ?? null;

    if (!$studentId) {
      return $this->json(['message' => 'Thiếu thông tin sinh viên.'], 422);
    }

    try {
      $this->_batchService->addStudentToBatch((int) $id, (int) $studentId);
      return $this->json([], 200, 'Thêm sinh viên thành công.');
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  /**
   * Xóa sinh viên khỏi đợt
   */
  public function removeStudent($id, $student_id)
  {
    try {
      $this->_batchService->removeStudentFromBatch((int) $id, (int) $student_id);
      return $this->json([], 200, 'Đã xóa sinh viên khỏi đợt.');
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  public function publishGrades($id, Request $request)
  {
    $adminId = $request->session()->authUser()['account_id'] ?? null;
    if (!$adminId) return $this->json(null, 401, 'Phiên đăng nhập không hợp lệ.');

    try {
      $this->_batchService->publishGrades((int)$adminId, (int)$id);
      return $this->json([], 200, 'Đã công bố điểm cho toàn đợt.');
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  public function adminUpdateGrade($id, $batch_student_id, Request $request)
  {
    $adminId = $request->session()->authUser()['account_id'] ?? null;
    if (!$adminId) return $this->json(null, 401, 'Phiên đăng nhập không hợp lệ.');

    $data = $request->json();
    $score = $data['grade'] ?? null;
    $reason = $data['score_reason'] ?? '';
    $feedback = $data['feedback'] ?? '';

    if ($score === null) {
      return $this->json(['message' => 'Vui lòng nhập điểm số.'], 422);
    }

    try {
      $result = $this->_gradeService->adminUpdateGrade((int)$batch_student_id, (float)$score, $reason, $feedback, (int)$adminId);
      if ($result['success']) {
        return $this->json([], 200, $result['message']);
      }
      return $this->json(['message' => $result['message']], 400);
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  /**
   * Lấy danh sách giảng viên trong đợt
   */
  public function getSupervisors($id)
  {
    try {
      $supervisors = $this->_batchService->getBatchSupervisors((int) $id);
      return $this->json($supervisors, 200);
    } catch (Exception $e) {
      return $this->json(null, 500, $e->getMessage());
    }
  }

  /**
   * Thêm giảng viên vào đợt
   */
  public function addSupervisor($id, Request $request)
  {
    $data = $request->json();

    // Hỗ trợ lưu nhiều giảng viên cùng lúc
    if (isset($data['teachers']) && is_array($data['teachers'])) {
      if (empty($data['teachers'])) {
        return $this->json(['message' => 'Danh sách giảng viên rỗng.'], 422);
      }

      try {
        $this->_batchService->addSupervisorsBulk((int) $id, $data['teachers']);
        return $this->json([], 200, 'Thêm các giảng viên thành công.');
      } catch (Exception $e) {
        return $this->json(null, 400, $e->getMessage());
      }
    }
    $teacherId = $data['teacher_id'] ?? null;
    $maxStudents = $data['max_students'] ?? 15;

    if (!$teacherId) {
      return $this->json(['message' => 'Thiếu thông tin giảng viên.'], 422);
    }

    try {
      $this->_batchService->addSupervisorToBatch((int) $id, (int) $teacherId, (int) $maxStudents);
      return $this->json([], 200, 'Thêm giảng viên thành công.');
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  /**
   * Cập nhật định mức giảng viên
   */
  public function updateSupervisor($id, $teacher_id, Request $request)
  {
    $data = $request->json();
    $newQuota = $data['max_students'] ?? null;

    if ($newQuota === null) {
      return $this->json(['message' => 'Thiếu định mức mới.'], 422);
    }

    try {
      $this->_batchService->updateSupervisorQuota((int) $id, (int) $teacher_id, (int) $newQuota);
      return $this->json([], 200, 'Cập nhật định mức thành công.');
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  /**
   * Xóa giảng viên khỏi đợt
   */
  public function removeSupervisor($id, $teacher_id)
  {
    try {
      $this->_batchService->removeSupervisorFromBatch((int) $id, (int) $teacher_id);
      return $this->json([], 200, 'Đã xóa giảng viên khỏi đợt.');
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  /**
   * Tìm kiếm sinh viên đủ điều kiện để thêm vào đợt
   */
  public function searchStudents($id, Request $request)
  {
    $query = $request->query('q') ?? '';
    $classroomId = $request->query('classroom_id') ? (int) $request->query('classroom_id') : null;

    try {
      $students = $this->_batchService->searchEligibleStudents((int) $id, $query, $classroomId);
      return $this->json($students, 200);
    } catch (Exception $e) {
      return $this->json(null, 500, $e->getMessage());
    }
  }

  /**
   * Tìm kiếm giảng viên đủ điều kiện để thêm vào đợt
   */
  public function searchTeachers($id, Request $request)
  {
    $query = $request->query('q') ?? '';

    try {
      $teachers = $this->_batchService->searchEligibleTeachers((int) $id, $query);
      return $this->json($teachers, 200);
    } catch (Exception $e) {
      return $this->json(null, 500, $e->getMessage());
    }
  }

  /**
   * Lấy danh sách giấy giới thiệu có phân trang
   */
  public function getPaginatedReferralLetters($id, Request $request)
  {
    $page = (int)$request->query('page', 1);
    $limit = (int)$request->query('limit', 20);
    $search = $request->query('search', '');
    $sortStr = $request->query('sort', '');
    $filters = $request->query('filters', []);

    if ($search !== '') {
      $filters[] = ['col' => 'student_search', 'op' => 'contains', 'value' => $search];
    }

    $sort = [];
    if ($sortStr) {
      $parts = explode(':', $sortStr);
      if (count($parts) === 2) {
        $sort = ['col' => $parts[0], 'dir' => $parts[1]];
      }
    }

    try {
      $data = $this->_referralLetterService->getPaginatedReferralLetters((int)$id, $page, $limit, $filters, $sort);
      return $this->json($data, 200);
    } catch (Exception $e) {
      return $this->json(null, 500, $e->getMessage());
    }
  }

  /**
   * Xử lý thao tác hàng loạt cho giấy giới thiệu
   */
  public function bulkActionReferralLetters($id, Request $request)
  {
    $data = $request->json();
    $ids = $data['ids'] ?? [];
    $action = $data['action'] ?? '';
    $reason = $data['reason'] ?? '';

    if (empty($ids) || !is_array($ids)) {
      return $this->json(null, 422, 'Không có giấy giới thiệu nào được chọn.');
    }

    $processedBy = $request->session()->authUser()['account_id'] ?? null;
    if (!$processedBy) {
      return $this->json(null, 401, 'Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.');
    }

    try {
      if ($action === 'reject') {
        if (empty(trim($reason))) {
          return $this->json(null, 422, 'Vui lòng nhập lý do từ chối giấy giới thiệu.');
        }
        $count = $this->_referralLetterService->bulkReview($ids, (int)$id, 'reject', trim($reason), $processedBy);
        return $this->json(['count' => $count], 200, "Đã từ chối {$count} giấy giới thiệu.");
      } elseif ($action === 'approve') {
        $count = $this->_referralLetterService->bulkReview($ids, (int)$id, 'approve', '', $processedBy);
        return $this->json(['count' => $count], 200, "Duyệt {$count} yêu cầu giấy giới thiệu.");
      } elseif ($action === 'complete') {
        $count = $this->_referralLetterService->bulkReview($ids, (int)$id, 'complete', '', $processedBy);
        return $this->json(['count' => $count], 200, "Đã hoàn thành {$count} giấy giới thiệu.");
      } else {
        return $this->json(null, 400, 'Thao tác với giấy giới thiệu không hợp lệ.');
      }
    } catch (Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  public function receiveReferralLetter($id, $letterId, Request $request)
  {
    $processedBy = $request->session()->authUser()['account_id'] ?? null;
    if (!$processedBy) return $this->json(null, 401, 'Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.');
    try {
      $success = $this->_referralLetterService->receive((int)$letterId, (int)$id, $request->json(), (int)$processedBy);
      return $success
        ? $this->json([], 200, 'Đã xác nhận sinh viên nhận giấy giới thiệu.')
        : $this->json(null, 400, 'Không thể cập nhật trạng thái nhận giấy giới thiệu.');
    } catch (Exception $e) {
      return $this->json(null, 422, $e->getMessage());
    }
  }
}
