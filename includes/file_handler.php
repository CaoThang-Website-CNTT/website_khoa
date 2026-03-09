<?php
class FileHandler
{
  public readonly int $FGETCSV_LENGTH = 100;
  public function __construct()
  {

  }
  public function processUpload($fileArray)
  {
    if (!isset($fileArray['error']) || is_array($fileArray['error'])) {
      throw new Exception("Tham số không hợp lệ");
    }

    if ($fileArray['error'] !== UPLOAD_ERR_OK) {
      throw new Exception("Upload thất bại với lỗi " . $fileArray['error']);
    }

    $tmpName = $fileArray['tmp_name'];
    $fileName = $fileArray['name'];

    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    switch ($extension) {
      case 'csv':
        $mimeType = mime_content_type($tmpName);
        $allowedCsvMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];

        if (!in_array($mimeType, $allowedCsvMimes)) {
          throw new Exception("Nội dung file không hợp lệ, Mong đợi file CSV.");
        }

        return $this->readCSV($tmpName);
      default:
        throw new Exception("Không hỗ trợ file có định dạng" . $extension);
    }
  }
  private function readCSV($filePath)
  {
    $data = [];

    if (($handle = fopen($filePath, "r")) !== false) {
      while (($row = fgetcsv($handle, $this->FGETCSV_LENGTH, ",")) !== false) {
        // Skip empty rows
        if (array_filter($row)) {
          $data[] = $row;
        }
      }
      fclose($handle);
    } else {
      throw new Exception("Không thể mở và đọc file CSV.");
    }

    return $data;
  }
}
?>