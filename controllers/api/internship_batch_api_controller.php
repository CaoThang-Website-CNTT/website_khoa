<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\InternshipBatchService;
use App\Core\Files\UploadedFileHandler;
use App\Core\Files\BatchStudentImporter;
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

  public function parseImport(Request $request)
  {
    try {
      $fileHandler = new UploadedFileHandler();

      // Hỗ trợ nhận một hoặc nhiều file cùng lúc
      $rawFiles = $request->file('file_import');

      if (!$rawFiles) {
        return $this->json(['message' => 'Vui lòng chọn ít nhất một file để upload.'], 400);
      }

      // Chuẩn hóa thành array để xử lý thống nhất (mảng của các file)
      // Nếu $rawFiles là 1 mảng assoc chứa key 'name', nó là 1 file đơn lẻ.
      if (isset($rawFiles['name'])) {
        $rawFiles = [$rawFiles];
      } else if (!is_array($rawFiles)) {
        $rawFiles = [$rawFiles];
      }

      $validClassroomNames = array_map(
        fn(array $classroom): string => BatchStudentImporter::normalizeClassroomName($classroom['name']),
        $this->_service->getAllClassrooms()
      );

      $allStudents = [];

      foreach ($rawFiles as $rawFile) {
        $uploadedFile = $fileHandler->processUpload($rawFile);

        if (!in_array($uploadedFile->extension, ['xls', 'xlsx'], true)) {
          return $this->json(['message' => "File '{$uploadedFile->originalName}': Chỉ hỗ trợ định dạng .xls hoặc .xlsx"], 400);
        }

        $students = BatchStudentImporter::import($uploadedFile->tmpPath);

        foreach ($students as &$student) {
          $normalizedName = BatchStudentImporter::normalizeClassroomName($student['classroom_name']);
          $student['is_classroom_invalid'] = !in_array($normalizedName, $validClassroomNames, true);
        }
        unset($student);

        $allStudents = array_merge($allStudents, $students);
      }

      return $this->json($allStudents, 200);
    } catch (\Throwable $e) {
      return $this->json(['message' => $e->getMessage()], 400, $e->getMessage());
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
      $validator = new RequestValidator();

      $rules = [
        'title' => ['required', 'max:255'],
        'start_at' => ['required', 'date'],
        'end_at' => ['required', 'date'],
        'students' => ['required'],
        'supervisors' => []
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
          'start_at' => $data['start_at'],
          'end_at' => $data['end_at']
        ],
        is_array($data['students']) ? $data['students'] : json_decode((string)$data['students'], true),
        isset($data['supervisors']) && is_array($data['supervisors']) ? $data['supervisors'] : (json_decode((string)($data['supervisors'] ?? '[]'), true) ?: []),
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
      ], 400, $e->getMessage());
    }
  }
}
