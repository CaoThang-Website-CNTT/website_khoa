<?php

require_once __DIR__ . '/../utils/request_validator.php';
require_once __DIR__ . '/../includes/core/request.php';
require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../includes/files/uploaded_file_handler.php';
require_once __DIR__ . '/../includes/files/xlsx_reader.php';
require_once __DIR__ . '/../includes/files/student_importer.php';


use App\Core\Request;
use App\Models\Student;
use App\Services\EducationRepositoryInterface;

class StudentImportController
{
  private $_educationService;

  public function __construct(EducationRepositoryInterface $educationService)
  {
    $this->_educationService = $educationService;
  }
  public function store(Request $request)
  {
    print_r($request->allFiles());

    $files = $request->allFiles();

    $handler = new UploadedFileHandler();

    try {
      $uploadedFile = $handler->processUpload($files['uploaded_file']);
      $result = StudentImporter::import($uploadedFile->tmpPath);
    } catch (Exception $e) {
      die("Import error: " . $e->getMessage());
    }

    echo "Class: {$result['class_name']}\n";
    echo "Total: " . count($result['students']) . " students\n\n";

    foreach ($result['students'] as $student) {
      echo "[{$student['stt']}] {$student['ho_ten']}"
        . " | SBD: {$student['sbd']}"
        . " | MaSV: {$student['ma_sv']}"
        . " | DOB: {$student['ngay_sinh']}\n";
    }
  }
}
?>