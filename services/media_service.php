<?php

namespace App\Services;

use App\Core\Files\UploadedFile;
use App\Core\Image\ImageProcessor;
use App\Core\Pageable;
use App\Models\Media;
use App\Stores\{MediaStore};

interface IMediaService
{
  // C
  public function create(UploadedFile $file, array $data, ?string $compressMode = null): Media;

  // R
  public function getMedias(int $page, int $limit = 15, ?string $search = null): Pageable;
  public function getMediaById(int $mediaId): ?Media;

  // U
  public function updateMetadata(int $mediaId, array $data): Media;

  // D
  public function delete(int $mediaId): void;
}

/**
 * MediaService - Xử lý logic nghiệp vụ và I/O file cho Media
 * Lưu file: {storageRoot}/media/YYYY/MM/{uuid}.webp (sau khi nén)
 */
class MediaService implements IMediaService
{
  private MediaStore $_mediaStore;
  private ImageProcessor $_imageProcessor;
  private string $_storageRoot;

  public function __construct(
    MediaStore $mediaStore,
    ImageProcessor $imageProcessor,
  ) {
    $this->_mediaStore = $mediaStore;
    $this->_imageProcessor = $imageProcessor;
    $this->_storageRoot = BASE_PATH . '/storage';
  }

  /**
   * Upload file từ HTTP multipart, hỗ trợ compress.
   * @param string|null $compressMode 'lossless'|'thumbnail'|'standard'|'banner'
   */
  public function create(UploadedFile $file, array $data, ?string $compressMode = null): Media
  {
    [$relativePath, $finalMime, $finalSize, $width, $height, $metadata] = $this->processAndSave(
      $file->tmpPath, 
      $file->originalName, 
      $compressMode, 
      isUploadedFile: true
    );

    $media = new Media(
      title: trim($data['title']) !== "" ? $data['title'] : pathinfo($file->originalName, PATHINFO_FILENAME),
      file_name: $file->originalName,
      file_path: $relativePath,
      mime_type: $finalMime,
      alt_text: $file->altText ?? '',
      width: $width,
      height: $height,
      file_size: $finalSize,
      metadata: $metadata
    );
    
    return $this->_mediaStore->create($media);
  }

  /**
   * Lấy danh sách media phân trang, hỗ trợ tìm kiếm.
   */
  public function getMedias(int $page, int $limit = 15, ?string $search = null): Pageable
  {
    $items = $this->_mediaStore->getPaginated($page, $limit, $search);
    $total = $this->_mediaStore->getTotalCount($search);
    return new Pageable($items, $total, $limit, $page);
  }

  public function getMediaById(int $mediaId): ?Media
  {
    return $this->_mediaStore->getById($mediaId);
  }

  public function updateMetadata(int $id, array $data): Media
  {
    $media = $this->getMediaById($id);
    if ($media === null) {
      throw new \InvalidArgumentException("Không tìm thấy Media #{$id}.");
    }
    return $this->_mediaStore->update($id, $data);
  }

  /**
   * Xóa file vật lý và record DB
   */
  public function delete(int $mediaId): void
  {
    $media = $this->getMediaById($mediaId);
    if ($media === null) {
      throw new \RuntimeException("Media #{$mediaId} không tồn tại.");
    }

    $absolutePath = $this->_storageRoot . '/' . ltrim($media->file_path, '/');

    if (file_exists($absolutePath) && !unlink($absolutePath)) {
      throw new \RuntimeException("Không thể xóa file vật lý: {$absolutePath}");
    }

    $this->_mediaStore->delete($mediaId);
  }

  // ---------------------------------------------------------------------------
  // Private helpers
  // ---------------------------------------------------------------------------

  /**
   * Xử lý ảnh (nén + convert nếu có ImageProcessor) và lưu vào storage.
   * Trả về [relativePath, mimeType, fileSize].
   */
  private function processAndSave(string $sourcePath, string $originalName, ?string $compressMode, bool $isUploadedFile): array
  {
    $relativDir = 'media/' . date('Y/m');
    $absoluteDir = $this->_storageRoot . '/' . ltrim($relativDir, '/');

    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, recursive: true)) {
      throw new \RuntimeException("Không thể tạo thư mục lưu trữ: {$absoluteDir}");
    }

    $baseFilename = $this->generateUuid();
    $width = null;
    $height = null;
    $metadata = [];

    if ($compressMode !== null && $this->_imageProcessor->supports($sourcePath)) {
      $variant = $this->_imageProcessor->processSingle($sourcePath, $absoluteDir, $baseFilename, $compressMode);

      if ($variant !== null) {
        $relativePath = $relativDir . '/' . basename($variant->relativePath);
        $absolutePath = $absoluteDir . '/' . basename($variant->relativePath);
        
        $imageInfo = @getimagesize($absolutePath);
        if ($imageInfo !== false) {
          $width = (int) $imageInfo[0];
          $height = (int) $imageInfo[1];
          $metadata = [
            'aspect_ratio' => $width > 0 && $height > 0 ? round($width / $height, 2) : null,
            'processed_mode' => $compressMode
          ];
        }

        return [$relativePath, $variant->mimeType, $variant->fileSize, $width, $height, $metadata];
      }
    }

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'bin';
    $fileName = $baseFilename . '.' . $extension;
    $absolutePath = $absoluteDir . '/' . $fileName;
    $relativePath = $relativDir . '/' . $fileName;

    if ($isUploadedFile) {
      if (!move_uploaded_file($sourcePath, $absolutePath)) {
        throw new \RuntimeException("Không thể di chuyển file upload đến: {$absolutePath}");
      }
    } else {
      if (!copy($sourcePath, $absolutePath)) {
        throw new \RuntimeException("Không thể sao chép file tạm đến: {$absolutePath}");
      }
    }

    $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';
    $fileSize = (int) filesize($absolutePath);

    if (str_starts_with($mime, 'image/')) {
      $imageInfo = @getimagesize($absolutePath);
      if ($imageInfo !== false) {
        $width = (int) $imageInfo[0];
        $height = (int) $imageInfo[1];
      }
    }

    return [$relativePath, $mime, $fileSize, $width, $height, empty($metadata) ? null : $metadata];
  }

  /**
   * Tạo UUID v4 theo RFC 4122
   */
  private function generateUuid(): string
  {
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
  }
}