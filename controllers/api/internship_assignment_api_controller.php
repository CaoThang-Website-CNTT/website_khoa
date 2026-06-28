<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Controller;
use App\Core\RequestValidator;
use App\Services\InternshipAssignmentService;
use App\Stores\InternshipAssignmentStore;
use Exception;

class InternshipAssignmentApiController extends Controller
{
  private InternshipAssignmentService $_assignmentService;
  private InternshipAssignmentStore $_assignmentStore;

  public function __construct(
    InternshipAssignmentService $assignmentService,
    InternshipAssignmentStore $assignmentStore
  ) {
    $this->_assignmentService = $assignmentService;
    $this->_assignmentStore = $assignmentStore;
  }

  /**
   * Lấy danh sách sinh viên kèm thông tin phân công (nếu có) của đợt
   */
  public function getAssignments($id)
  {
    try {
      $assignments = $this->_assignmentStore->getStudentsInBatchWithAssignment((int) $id);
      return $this->json($assignments, 200);
    } catch (Exception $e) {
      return $this->json(['message' => 'Lỗi khi tải danh sách phân công: ' . $e->getMessage()], 500);
    }
  }

  /**
   * Lấy danh sách giảng viên kèm thống kê Quota của đợt
   */
  public function getSupervisors($id)
  {
    try {
      $supervisors = $this->_assignmentStore->getBatchSupervisorsWithStats((int) $id);
      return $this->json($supervisors, 200);
    } catch (Exception $e) {
      return $this->json(['message' => 'Lỗi khi tải danh sách giảng viên: ' . $e->getMessage()], 500);
    }
  }

  /**
   * Phân công tự động
   */
  public function autoAssign($id, Request $request)
  {
    $data = $request->json();
    $validator = new RequestValidator();

    $rules = [
      'method' => ['required', 'in:auto_even,auto_shuffle']
    ];

    if (!$validator->validate($data, $rules)) {
      return $this->json(['errors' => $validator->getErrors()], 422, 'Dữ liệu không hợp lệ.');
    }

    try {
      $adminId = (int) ($request->session()->authUser()['account_id'] ?? 0);
      if ($adminId < 1)
        return $this->json(['message' => 'Chưa xác thực.'], 401);

      $assignedCount = $this->_assignmentService->autoAssign((int) $id, $data['method'], $adminId);
      return $this->json(['assigned_count' => $assignedCount], 200, 'Đã phân công tự động thành công ' . $assignedCount . ' sinh viên.');
    } catch (Exception $e) {
      return $this->json(['message' => $e->getMessage()], 400);
    }
  }

  /**
   * Lưu và công bố
   */
  public function bulkSave($id, Request $request)
  {
    $data = $request->json();
    $validator = new RequestValidator();

    $rules = [
      'assignments' => ['required'], // Array of {assignment_id, new_teacher_id}
      'reason' => ['required']
    ];

    if (!$validator->validate($data, $rules)) {
      return $this->json(['errors' => $validator->getErrors()], 422, 'Dữ liệu không hợp lệ.');
    }

    if (!is_array($data['assignments'])) {
      return $this->json(['message' => 'Định dạng danh sách phân công không hợp lệ.'], 422);
    }

    try {
      $adminId = (int) ($request->session()->authUser()['account_id'] ?? 0);
      if ($adminId < 1)
        return $this->json(['message' => 'Chưa xác thực.'], 401);

      $this->_assignmentService->bulkSave(
        (int) $id,
        $data['assignments'],
        $adminId,
        $data['reason']
      );

      return $this->json([], 200, 'Đã lưu thay đổi thành công.');
    } catch (Exception $e) {
      return $this->json(['message' => $e->getMessage()], 400);
    }
  }
}
