<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Controller;
use App\Core\Validator;
use App\Services\StudentService;
use Exception;

class StudentApiController extends Controller
{
  private StudentService $_studentService;
  public function __construct(StudentService $studentService)
  {
    $this->_studentService = $studentService;
  }
  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;
    $perPage = $request->query('limit') ?? 15;

    try {
      $students = $this->_studentService->getStudents($currentPage, $perPage);
      return $this->json($students, 200);
    } catch (Exception $e) {
      return $this->json(['message' => 'Không tìm thấy dữ liệu yêu cầu.'], 404);
    }
  }
  public function show($student_id)
  {
    try {
      $student = $this->_studentService->getStudentByStudentId($student_id);
      return $this->json($student, 200);
    } catch (Exception $e) {
      return $this->json(['message' => 'Không tìm thấy dữ liệu yêu cầu.'], 404);
    }
  }
  public function store(Request $request)
  {
    $data = $request->json();
    $validator = new Validator();

    $rules = [
      'full_name' => ['required', 'max:255'],
      'dob' => ['required', 'date'],
      'birth_place' => ['required', 'max:255'],
      'national_id' => ['required', 'size:12'],
      'gender' => ['required', 'in:male,female'],
      'phone' => ['required', 'phone', 'max:15'],
      'address' => ['required'],
      'student_id' => ['required', 'size:10'],
      'classroom_id' => ['required'],
      'notes' => ['nullable'],
      'status' => ['required', 'in:Đang học,Đã tốt nghiệp,Tạm ngưng,Thôi học'],
    ];

    if (!$validator->validate($data, $rules)) {
      return $this->json(
        ['errors' => $validator->getErrors()],
        422,
        'Dữ liệu không hợp lệ.'
      );
    }

    if (!$this->_studentService->isStudentIdUnique($data['student_id'])) {
      $validator->addError('student_id', 'Mã sinh viên này đã tồn tại.');
      return $this->json(
        ['errors' => $validator->getErrors()],
        422,
        'Dữ liệu không hợp lệ.'
      );
    }

    $student = $this->_studentService->createStudent($data);

    return $this->json($student, 201, 'Tạo sinh viên thành công.');
  }
  public function update($student_id, Request $request)
  {
    $data = $request->json();
    $validator = new Validator();

    $rules = [
      'full_name' => ['max:255'],
      'dob' => ['date'],
      'birth_place' => ['max:255'],
      'national_id' => ['size:12'],
      'gender' => ['in:male,female'],
      'phone' => ['phone', 'max:15'],
      'address' => ['nullable'],
      'classroom_id' => ['nullable'],
      'notes' => ['nullable'],
      'status' => ['in:Đang học,Đã tốt nghiệp,Tạm ngưng,Thôi học'],
    ];

    $activeRules = array_intersect_key($rules, $data);

    if (!empty($activeRules) && !$validator->validate($data, $activeRules)) {
      return $this->json(
        ['errors' => $validator->getErrors()],
        422,
        'Dữ liệu không hợp lệ.'
      );
    }

    $student = $this->_studentService->updateStudent($student_id, $data);

    return $this->json($student->toArray(), 200, 'Cập nhật sinh viên thành công.');
  }
  public function destroy($student_id)
  {
    try {
      $student = $this->_studentService->deleteStudent($student_id);
      return $this->json($student, 204);
    } catch (Exception $e) {
      return $this->json(['message' => 'Không tìm thấy dữ liệu yêu cầu.'], 404);
    }
  }
}