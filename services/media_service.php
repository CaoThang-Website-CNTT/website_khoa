<?php

namespace App\Services;

use App\Core\Files\UploadedFile;
use App\Core\Image\ImageProcessor;
use App\Core\Pageable;
use App\Models\Media;
use App\Stores\{CarouselStore, MediaStore};

interface IMediaService
{
  public function getMedias(int $page, int $limit = 15, ?string $search = null): Pageable;
  public function getMediaById(int $mediaId): ?Media;
  public function upload(UploadedFile $file, ?int $postId = null, ?string $compressMode = null): Media;
  public function updateMetadata(int $mediaId, array $data): Media;
  public function delete(int $mediaId): void;
  public function attachToPost(array $mediaIds, int $postId): void;
  public function getByPostId(int $postId): array;
  public function deleteOrphans(\DateTimeInterface $olderThan): int;
}

/**
 * MediaService - Xử lý logic nghiệp vụ và I/O file cho Media
 * Lưu file: {storageRoot}/media/YYYY/MM/{uuid}.webp (sau khi nén)
 */
class MediaService implements IMediaService
{
  private MediaStore $_mediaStore;
  private CarouselStore $_carouselStore;
  private ImageProcessor $_imageProcessor;
  private string $_storageRoot;

  /** Dung lượng tối đa tải về từ URL (bytes). Đọc từ ENV MAX_UPLOAD_SIZE (MB). */
  private int $_maxUrlBytes;

  public function __construct(
    MediaStore $mediaStore,
    ImageProcessor $imageProcessor,
  ) {
    $this->_mediaStore = $mediaStore;
    $this->_imageProcessor = $imageProcessor;
    $this->_storageRoot = BASE_PATH . '/storage';

    $maxMb = (int) ($_ENV['MAX_UPLOAD_SIZE'] ?? 5);
    $this->_maxUrlBytes = $maxMb * 1024 * 1024;
  }

  /**
   * Upload file từ HTTP multipart, hỗ trợ compress.
   * @param string|null $compressMode 'lossless'|'thumbnail'|'standard'|'banner'
   */
  public function upload(UploadedFile $file, ?int $postId = null, ?string $compressMode = null): Media
  {
    [$relativePath, $finalMime, $finalSize] = $this->processAndSave($file->tmpPath, $file->originalName, $compressMode, isUploadedFile: true);

    $media = new Media(
      file_name: $file->originalName,
      file_path: $relativePath,
      mime_type: $finalMime,
      file_size: $finalSize,
      alt_text: $file->altText ?? '',
      post_id: $postId,
    );
    
    return $this->_mediaStore->create($media);
  }

  /**
   * Xóa file vật lý và record DB
   */
  public function delete(int $mediaId): void
  {
    $media = $this->getOrFail($mediaId);
    $absolutePath = $this->_storageRoot . '/' . ltrim($media->file_path, '/');

    if (file_exists($absolutePath) && !unlink($absolutePath)) {
      throw new \RuntimeException("Không thể xóa file vật lý: {$absolutePath}");
    }

    $this->_mediaStore->delete($mediaId);
  }

  public function getMediaById(int $mediaId): ?Media
  {
    return $this->_mediaStore->getById($mediaId);
  }

  /**
   * Gắn nhiều media vào một post
   */
  public function attachToPost(array $mediaIds, int $postId): void
  {
    if (empty($mediaIds)) {
      return;
    }
    $this->_mediaStore->attachToPost($mediaIds, $postId);
  }

  /**
   * Lấy danh sách media theo post_id
   */
  public function getByPostId(int $postId): array
  {
    return $this->_mediaStore->findByPostId($postId);
  }

  /**
   * Cập nhật metadata (alt_text, post_id...). file_path là immutable
   */
  public function updateMetadata(int $mediaId, array $data): Media
  {
    $this->getOrFail($mediaId);

    // Chặn sửa các field immutable ở service layer
    unset($data['file_path'], $data['id'], $data['created_at']);

    return $this->_mediaStore->update($mediaId, $data);
  }

  /**
   * Xóa các media orphan (không có post và không dùng trong carousel_slides) cũ hơn mốc thời gian
   */
  public function deleteOrphans(\DateTimeInterface $olderThan): int
  {
    $orphans = $this->_mediaStore->findOrphansOlderThan($olderThan);
    $deleted = 0;

    foreach ($orphans as $media) {
      // Kiểm tra xem media có đang dùng trong carousel_slides không
      if ($this->isUsed($media->file_path)) {
        continue;
      }

      try {
        $this->delete($media->id);
        $deleted++;
      } catch (\RuntimeException) {
        // Lỗi thì bỏ qua, tiếp tục xử lý file khác
        continue;
      }
    }

    return $deleted;
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

    // Thử xử lý qua ImageProcessor nếu file là ảnh được hỗ trợ
    if ($compressMode !== null && $this->_imageProcessor->supports($sourcePath)) {
      $variant = $this->_imageProcessor->processSingle($sourcePath, $absoluteDir, $baseFilename, $compressMode);

      if ($variant !== null) {
        // Lưu thành công qua processor — xóa file gốc nếu không phải uploaded_file PHP
        // (uploaded_file sẽ tự bị xóa sau request; file tạm từ URL cần xóa thủ công ở caller)
        $relativePath = $relativDir . '/' . basename($variant->relativePath);
        return [$relativePath, $variant->mimeType, $variant->fileSize];
      }
    }

    // Fallback: không nén — lưu file gốc thẳng
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
    return [$relativePath, $mime, (int) filesize($absolutePath)];
  }

  /**
   * Kiểm tra file_path có đang dùng trong bảng carousel_slides không.
   */
  private function isUsed(string $filePath): bool
  {
    try {
      return $this->_carouselStore->isImageUsed($filePath);
    } catch (\Throwable) {
      return false;
    }
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

  /**
   * Lấy Media theo ID hoặc throw exception nếu không tìm thấy
   */
  private function getOrFail(int $mediaId): Media
  {
    $media = $this->_mediaStore->getById($mediaId);

    if ($media === null) {
      throw new \RuntimeException("Media #{$mediaId} không tồn tại.");
    }

    return $media;
  }
}