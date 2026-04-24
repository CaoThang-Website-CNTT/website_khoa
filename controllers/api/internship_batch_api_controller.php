<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Services\InternshipBatchService;
use Exception;

class InternshipBatchApiController extends Controller
{
  private InternshipBatchService $_service;

  public function __construct(InternshipBatchService $service)
  {
    $this->_service = $service;
  }

  public function getClassrooms()
  {
    try {
      $classrooms = $this->_service->getAllClassrooms();
      return $this->json($classrooms, 200);
    } catch (Exception $e) {
      return $this->json(['message' => $e->getMessage()], 500);
    }
  }

  public function getEligibleStudents(Request $request)
  {
    $classroomIdsParam = $request->query('classroom_ids');

    if (empty($classroomIdsParam)) {
      return $this->json([], 200);
    }

    try {
      if (is_array($classroomIdsParam)) {
        $classroomIds = array_map('intval', $classroomIdsParam);
        $students = $this->_service->getEligibleStudentsByClassrooms($classroomIds);
      } else {
        $students = $this->_service->getEligibleStudentsByClassroom((int)$classroomIdsParam);
      }
      return $this->json($students, 200);
    } catch (Exception $e) {
      return $this->json(['message' => $e->getMessage()], 500);
    }
  }

  public function validateStudentsBulk(Request $request)
  {
    $data = $request->json();
    if (empty($data['student_ids']) || !is_array($data['student_ids'])) {
      return $this->json(['message' => 'Vui lòng cung cấp danh sách mssv'], 400);
    }

    try {
      $result = $this->_service->validateStudentsBulk($data['student_ids']);
      return $this->json($result, 200);
    } catch (Exception $e) {
      return $this->json(['message' => $e->getMessage()], 500);
    }
  }

  public function getActiveTeachers()
  {
    try {
      $teachers = $this->_service->getActiveTeachers();
      return $this->json($teachers, 200);
    } catch (Exception $e) {
      return $this->json(['message' => $e->getMessage()], 500);
    }
  }

  public function store(Request $request)
  {
    ob_start();
    try {
      $data = $request->json();
      $validator = new Validator();

      $rules = [
        'title' => ['required', 'max:255'],
        'class_of' => ['required'],
        'level' => ['required', 'in:CĐ,CĐN'],
        'start_at' => ['required', 'date'],
        'end_at' => ['required', 'date'],
        'student_ids' => ['required'],
        'classroom_ids' => ['required'],
        'supervisors' => ['required']
      ];

      if (!$validator->validate($data, $rules)) {
        ob_end_clean();
        return $this->json(['errors' => $validator->getErrors()], 422, 'Dữ liệu không hợp lệ.');
      }

      if (strtotime($data['end_at']) <= strtotime($data['start_at'])) {
        ob_end_clean();
        return $this->json(['errors' => ['end_at' => 'Ngày kết thúc phải sau ngày bắt đầu.']], 422, 'Dữ liệu không hợp lệ.');
      }

      // Giả lập adminId (sau này đổi thành lấy từ Session/Auth)
      $adminId = 1;

      $batchId = $this->_service->createFullBatch(
        [
          'title' => $data['title'],
          'description' => $data['description'] ?? null,
          'class_of' => $data['class_of'],
          'level' => $data['level'],
          'start_at' => $data['start_at'],
          'end_at' => $data['end_at']
        ],
        $data['student_ids'],
        $data['supervisors'],
        $data['classroom_ids'],
        $adminId
      );

      $output = ob_get_clean();
      if (!empty($output)) {
        return $this->json([
          'batch_id' => $batchId,
          'debug_output' => $output
        ], 201, 'Tạo đợt thực tập thành công.');
      }

      return $this->json(['batch_id' => $batchId], 201, 'Tạo đợt thực tập thành công.');
    } catch (Exception $e) {
      $output = ob_get_clean();
      return $this->json([
        'message' => $e->getMessage(),
        'debug_output' => $output
      ], 400);
    }
  }
}
