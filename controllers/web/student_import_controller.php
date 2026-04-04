<?php

namespace App\Controllers;

require_once BASE_PATH . '/includes/files/uploaded_file_handler.php';
require_once BASE_PATH . '/includes/files/xlsx_reader.php';
require_once BASE_PATH . '/includes/files/student_importer.php';

use App\Core\Request;
use App\Core\Files\{UploadedFileHandler, StudentImporter};
use App\Services\EducationService;

class StudentImportController
{
  private EducationService $_educationService;

  public function __construct(EducationService $educationService)
  {
    $this->_educationService = $educationService;
  }

  public function store(Request $request): void
  {
    $files = $request->allFiles();

    try {
      $handler = new UploadedFileHandler();
      $uploadedFile = $handler->processUpload($files['uploaded_file']);
      $result = StudentImporter::import($uploadedFile->tmpPath);
    } catch (\RuntimeException $e) {
      $this->redirectWithError('Không thể đọc file: ' . $e->getMessage());
      return;
    }

    $className = trim($result['class_name'] ?? '');
    $rows = $result['students'] ?? [];

    if (empty($rows)) {
      $this->redirectWithError('File không có dữ liệu sinh viên.');
      return;
    }

    if (empty($className)) {
      $this->redirectWithError('Không tìm thấy tên lớp trong file.');
      return;
    }

    $errors = [];
    foreach ($rows as $index => $row) {
      $rowErrors = $this->validateRow($row, $index + 1);
      if (!empty($rowErrors)) {
        $errors[] = $rowErrors;
      }
    }

    if (!empty($errors)) {
      $_SESSION['import_errors'] = $errors;
      header('Location: ' . url('admin/students/import'));
      exit;
    }

    try {
      $classroomId = $this->resolveClassroom($className);
      $mapped = array_map(fn($row) => $this->mapRow($row, $classroomId), $rows);

      $this->_educationService->importStudents($mapped);

      flash('success', 'Nhập danh sách thành công!', count($rows) . ' sinh viên đã được thêm vào hệ thống.');
    } catch (\RuntimeException $e) {
      $this->redirectWithError('Lỗi khi lưu dữ liệu: ' . $e->getMessage());
      return;
    }

    header('Location: ' . url('admin/students'));
    exit;
  }

  private function mapRow(array $row, int $classroomId): array
  {
    return [
      'student_id' => $row['ma_sv'],
      'full_name' => mb_convert_case($row['ho_ten'], MB_CASE_TITLE, 'UTF-8'),
      'dob' => $this->normalizeDate($row['ngay_sinh'] ?? ''),
      'birth_place' => $row['noi_sinh'] ?? null,
      'classroom_id' => $classroomId,
      'gender' => null,
      'phone' => null,
      'major' => null,
      'password' => $row['ma_sv'],
    ];
  }

  private function validateRow(array $row, int $lineNumber): array
  {
    $errors = [];
    $label = 'Dòng ' . $lineNumber . ' (' . mb_convert_case($row['ho_ten'], MB_CASE_TITLE, 'UTF-8') . ')';

    if (empty($row['ma_sv'])) {
      $errors[] = "{$label}: Mã sinh viên không được để trống.";
    } elseif (!$this->_educationService->isStudentIdUnique($row['ma_sv'])) {
      $errors[] = "{$label}: Mã sinh viên '{$row['ma_sv']}' đã tồn tại trong hệ thống.";
    }

    if (empty($row['ho_ten'])) {
      $errors[] = "{$label}: Họ tên không được để trống.";
    }

    if (!empty($row['ngay_sinh']) && !$this->isValidDate($row['ngay_sinh'])) {
      $errors[] = "{$label}: Ngày sinh '{$row['ngay_sinh']}' không hợp lệ.";
    }

    return $errors;
  }

  private function resolveClassroom(string $name): int
  {
    foreach ($this->_educationService->getAllClassrooms() as $classroom) {
      if (trim($classroom->name) === $name) {
        return $classroom->id;
      }
    }

    return $this->_educationService->createClassroom(['name' => $name]);
  }

  private function normalizeDate(string $raw): ?string
  {
    if (empty($raw))
      return null;

    $dt = \DateTime::createFromFormat('d/m/Y', $raw)
      ?? \DateTime::createFromFormat('j/n/Y', $raw)
      ?? \DateTime::createFromFormat('Y-m-d', $raw);

    return $dt ? $dt->format('Y-m-d') : null;
  }

  private function isValidDate(string $raw): bool
  {
    return $this->normalizeDate($raw) !== null;
  }

  private function redirectWithError(string $message): void
  {
    $_SESSION['import_errors'] = [[$message]];
    header('Location: ' . url('admin/students/import'));
    exit;
  }
}