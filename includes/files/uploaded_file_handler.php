<?php

namespace App\Core\Files;

/**
 * UploadedFile
 *
 * Simple value object representing a validated uploaded file.
 * Carries only what callers need — no parsing logic.
 */
class UploadedFile
{
  public function __construct(
    public string $tmpPath,
    public string $originalName,
    public string $extension,
    public string $mimeType,
    public int $fileSize,
    public int $altText,
  ) {
  }
}

/**
 * UploadedFileHandler
 *
 * Validates a $_FILES entry and returns an UploadedFile.
 * Knows nothing about how the file will be parsed downstream.
 */
class UploadedFileHandler
{
  /** Extensions the application accepts, lowercase. */
  private const ALLOWED_EXTENSIONS = [
    'xlsx',

    'jpg',
    'jpeg',
    'png',
    'gif',
    'webp',
    'avif',
    'ico',
  ];

  /**
   * Validate a $_FILES entry and return a safe UploadedFile.
   *
   * @param  array $fileArray  A single entry from $_FILES.
   * @return UploadedFile
   * @throws \Exception on any validation failure.
   */
  public function processUpload(array $fileArray): UploadedFile
  {
    $this->assertValidStructure($fileArray);
    $this->assertNoUploadError($fileArray['error']);

    $extension = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
    $this->assertAllowedExtension($extension);

    return new UploadedFile(
      tmpPath: $fileArray['tmp_name'],
      originalName: $fileArray['name'],
      extension: $extension,
      mimeType
    );
  }

  // -------------------------------------------------------------------------

  private function assertValidStructure(array $fileArray): void
  {
    if (!isset($fileArray['error']) || is_array($fileArray['error'])) {
      throw new \Exception("Tham số không hợp lệ.");
    }
  }

  private function assertNoUploadError(int $errorCode): void
  {
    if ($errorCode !== UPLOAD_ERR_OK) {
      throw new \Exception("Upload thất bại với lỗi: {$errorCode}");
    }
  }

  private function assertAllowedExtension(string $extension): void
  {
    if (!in_array($extension, self::ALLOWED_EXTENSIONS, strict: true)) {
      throw new \Exception("Không hỗ trợ file có định dạng: .{$extension}");
    }
  }
}
?>