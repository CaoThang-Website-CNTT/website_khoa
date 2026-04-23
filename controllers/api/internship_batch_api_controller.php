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
    $classroomId = $request->query('classroom_id');
    if (!$classroomId) {
      return $this->json(['message' => 'Vui lòng cung cấp classroom_id'], 400);
    }
    
    try {
      $students = $this->_service->getEligibleStudentsByClassroom((int)$classroomId);
      return $this->json($students, 200);
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
    $data = $request->json();
    $validator = new Validator();
    
    $rules = [
      'title' => ['required', 'max:255'],
      'class_of' => ['required'],
      'level' => ['required', 'in:CĐ,CĐN'],
      'start_at' => ['required', 'date'],
      'end_at' => ['required', 'date'],
      'student_ids' => ['required'], // Array
      'classroom_ids' => ['required'], // Array
      'supervisors' => ['required'] // Array of {teacher_id, max_students}
    ];

    if (!$validator->validate($data, $rules)) {
      return $this->json(['errors' => $validator->getErrors()], 422, 'Dữ liệu không hợp lệ.');
    }

    if (strtotime($data['end_at']) <= strtotime($data['start_at'])) {
      return $this->json(['errors' => ['end_at' => 'Ngày kết thúc phải sau ngày bắt đầu.']], 422, 'Dữ liệu không hợp lệ.');
    }

    try {
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

      return $this->json(['batch_id' => $batchId], 201, 'Tạo đợt thực tập thành công.');
    } catch (Exception $e) {
      return $this->json(['message' => $e->getMessage()], 400);
    }
  }
}
