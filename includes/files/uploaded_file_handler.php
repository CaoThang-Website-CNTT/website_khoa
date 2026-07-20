<?php

namespace App\Core\Files;

/**
 * UploadedFile - Value object bất biến cho file upload đã validate
 */
class UploadedFile
{
  public function __construct(
    public string $tmpPath,
    public string $originalName,
    public string $extension,
    public string $mimeType,
    public int $fileSize,
    public ?string $altText = null,
  ) {}
}


/**
 * UploadedFileHandler - Validate và xử lý file upload
 * Kiểm tra: extension + MIME type (finfo)
 */
class UploadedFileHandler
{
  /** MIME types được phép */
  private const ALLOWED_MIME_TYPES = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'image/avif',
    'image/x-icon',
    'image/vnd.microsoft.icon',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
    'application/vnd.ms-excel', // .xls
    'application/msexcel', // .xls (alternative)
    'application/x-msexcel', // .xls (alternative)
    'application/zip',
    'application/x-zip-compressed',
    'application/x-rar',
    'application/x-rar-compressed',
    'application/pdf',
  ];

  /** Extensions được phép */
  private const ALLOWED_EXTENSIONS = [
    'jpg',
    'jpeg',
    'png',
    'gif',
    'webp',
    'avif',
    'ico',
    'xlsx',
    'xls',
    'zip',
    'rar',
    'pdf',
  ];

  private const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB

  /**
   * Validate $_FILES array và trả về UploadedFile
   */
  public function processUpload(array $fileArray, ?string $altText = null): UploadedFile
  {
    $this->assertValidStructure($fileArray);
    $this->assertNoUploadError($fileArray['error']);

    $extension = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
    $this->assertAllowedExtension($extension);

    $maxMb = (int) ($_ENV['MAX_UPLOAD_SIZE'] ?? 5);
    $maxBytes = $maxMb * 1024 * 1024;
    if ((int) $fileArray['size'] > $maxBytes) {
      throw new \RuntimeException("Kích thước tệp vượt quá giới hạn cấu hình {$maxMb}MB.");
    }


    $mimeType = $this->detectMimeType($fileArray['tmp_name']);
    $this->assertAllowedMimeType($mimeType);

    $this->assertAllowedSize((int) $fileArray['size']);

    return new UploadedFile(
      tmpPath: $fileArray['tmp_name'],
      originalName: $fileArray['name'],
      extension: $extension,
      mimeType: $mimeType,
      fileSize: (int) $fileArray['size'],
      altText: $altText,
    );
  }

  private function assertAllowedSize(int $size): void
  {
    if ($size > self::MAX_FILE_SIZE) {
      $maxMb = self::MAX_FILE_SIZE / 1024 / 1024;
      throw new \RuntimeException("Dung lượng file vượt quá giới hạn cho phép ({$maxMb}MB).");
    }
  }

  private function assertValidStructure(array $fileArray): void
  {
    $required = ['error', 'name', 'tmp_name', 'size'];
    foreach ($required as $key) {
      if (!array_key_exists($key, $fileArray)) {
        throw new \RuntimeException("Cấu trúc file không hợp lệ: thiếu trường '{$key}'.");
      }
    }

    if (is_array($fileArray['error'])) {
      throw new \RuntimeException("Chỉ chấp nhận upload từng file một.");
    }
  }

  private function assertNoUploadError(int $errorCode): void
  {
    $messages = [
      UPLOAD_ERR_INI_SIZE => "File vượt quá giới hạn upload_max_filesize trong php.ini.",
      UPLOAD_ERR_FORM_SIZE => "File vượt quá giới hạn MAX_FILE_SIZE trong form.",
      UPLOAD_ERR_PARTIAL => "File chỉ được upload một phần.",
      UPLOAD_ERR_NO_FILE => "Không có file nào được upload.",
      UPLOAD_ERR_NO_TMP_DIR => "Thiếu thư mục tạm thời.",
      UPLOAD_ERR_CANT_WRITE => "Không thể ghi file lên đĩa.",
      UPLOAD_ERR_EXTENSION => "Một PHP extension đã chặn upload.",
    ];

    if ($errorCode !== UPLOAD_ERR_OK) {
      $message = $messages[$errorCode] ?? "Upload thất bại với lỗi không xác định: {$errorCode}";
      throw new \RuntimeException($message);
    }
  }

  private function assertAllowedExtension(string $extension): void
  {
    if (!in_array($extension, self::ALLOWED_EXTENSIONS, strict: true)) {
      throw new \RuntimeException("Không hỗ trợ file có định dạng: .{$extension}");
    }
  }

  private function assertAllowedMimeType(string $mimeType): void
  {
    if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, strict: true)) {
      throw new \RuntimeException("MIME type không được phép: {$mimeType}");
    }
  }

  /**
   * Đọc magic bytes để xác định MIME type thực tế
   */
  private function detectMimeType(string $tmpPath): string
  {
    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpPath);

    if ($mime === false) {
      throw new \RuntimeException("Không thể xác định MIME type của file.");
    }

    return $mime;
  }
}
