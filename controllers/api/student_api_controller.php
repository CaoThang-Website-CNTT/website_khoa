<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Controller;
use App\Core\RequestValidator;
use App\Services\{StudentService, MediaService, InternshipSubmissionService};
use App\Core\Files\UploadedFileHandler;
use Exception;

class StudentApiController extends Controller
{
  private StudentService $_studentService;
  private MediaService $_mediaService;
  private InternshipSubmissionService $_submissionService;
  private UploadedFileHandler $_fileHandler;

  public function __construct(
    StudentService $studentService,
    MediaService $mediaService,
    InternshipSubmissionService $submissionService,
    UploadedFileHandler $fileHandler
  ) {
    $this->_studentService = $studentService;
    $this->_mediaService = $mediaService;
    $this->_submissionService = $submissionService;
    $this->_fileHandler = $fileHandler;
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
    $validator = new RequestValidator();

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
    $validator = new RequestValidator();

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

  public function updateProfile(Request $request)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser || $authUser['role'] !== 'student') {
      return $this->json(['message' => 'Không có quyền truy cập.'], 403);
    }

    $data = $request->json();
    $validator = new RequestValidator();

    $rules = [
      'full_name' => ['required', 'max:255'],
      'dob' => ['required', 'date'],
      'birth_place' => ['required', 'max:255'],
      'gender' => ['required', 'in:male,female,other'],
      'phone' => ['required', 'phone', 'max:15'],
      'address' => ['required'],
    ];

    if (!$validator->validate($data, $rules)) {
      return $this->json(
        ['errors' => $validator->getErrors()],
        422,
        'Dữ liệu không hợp lệ.'
      );
    }

    try {
      $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
      if (!$student) {
        return $this->json(['message' => 'Không tìm thấy hồ sơ sinh viên.'], 404);
      }

      // We only allow updating specific fields
      $updateData = [
        'full_name' => $data['full_name'],
        'dob' => $data['dob'],
        'birth_place' => $data['birth_place'],
        'gender' => $data['gender'],
        'phone' => $data['phone'],
        'address' => $data['address'],
      ];

      $updatedStudent = $this->_studentService->updateStudent($student->student_id, $updateData);
      return $this->json(['success' => true, 'data' => $updatedStudent->toArray()], 200, 'Cập nhật thành công.');
    } catch (Exception $e) {
      return $this->json(['message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
    }
  }

  public function uploadDocument(Request $request)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser || $authUser['role'] !== 'student') {
      return $this->json(['message' => 'Không có quyền truy cập.'], 403);
    }

    $batchStudentId = $request->input('batch_student_id');
    $type = $request->input('type', 'internship_report');

    if (!$batchStudentId) {
      return $this->json(['message' => 'Thiếu thông tin đợt thực tập.'], 422);
    }

    try {
      $uploadedFile = $this->_fileHandler->processUpload($request->file('file'));
      if (!$uploadedFile) {
        throw new Exception("Không tìm thấy file tải lên.");
      }

      $media = $this->_mediaService->create($uploadedFile, ['title' => $uploadedFile->originalName]);

      $this->_submissionService->createSubmission((int)$batchStudentId, [
        'type' => $type,
        'storage_mode' => 'file',
        'file_path' => $media->file_path,
      ]);

      return $this->json([
        'success' => true,
        'message' => 'Tải lên tài liệu thành công.',
        'media' => $media->toArray()
      ], 200);
    } catch (\Exception $e) {
      return $this->json(['message' => $e->getMessage()], 400);
    }
  }
}
