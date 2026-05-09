<?php

namespace App\Services;

use App\Core\Files\UploadedFile;
use App\Models\Media;
use App\Stores\MediaStore;

interface IMediaService
{
  public function upload(UploadedFile $file, ?int $postId = null): Media;
  public function delete(int $mediaId): void;
  public function getMedia(int $mediaId): ?Media;
  public function attachToPost(array $mediaIds, int $postId): void;
  public function getByPostId(int $postId): array;
  public function updateMetadata(int $mediaId, array $data): Media;
  public function deleteOrphans(\DateTimeInterface $olderThan): int;
}

/**
 * MediaService - Xử lý logic nghiệp vụ và I/O file cho Media
 * Lưu file: {storageRoot}/media/YYYY/MM/{uuid}.{ext}
 */
class MediaService implements IMediaService
{
  private MediaStore $_mediaStore;
  private string $_storageRoot;

  public function __construct(
    MediaStore $mediaStore,
  ) {
    $this->_mediaStore = $mediaStore;
    $this->_storageRoot = "storage";
  }

  /**
   * Di chuyển file upload vào storage và lưu metadata vào DB
   */
  public function upload(UploadedFile $file, ?int $postId = null): Media
  {
    $relativePath = $this->moveToStorage($file);

    $media = new Media(
      file_name: $file->originalName,
      file_path: $relativePath,
      mime_type: $file->mimeType,
      file_size: $file->fileSize,
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
    $absolutePath = $this->absolutePath($media->file_path);

    if (file_exists($absolutePath) && !unlink($absolutePath)) {
      throw new \RuntimeException("Không thể xóa file vật lý: {$absolutePath}");
    }

    $this->_mediaStore->delete($mediaId);
  }

  public function getMedia(int $mediaId): ?Media
  {
    return $this->_mediaStore->findById($mediaId);
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
   * Xóa các media orphan (không có post) cũ hơn mốc thời gian chỉ định
   */
  public function deleteOrphans(\DateTimeInterface $olderThan): int
  {
    $orphans = $this->_mediaStore->findOrphansOlderThan($olderThan);
    $deleted = 0;

    foreach ($orphans as $media) {
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
   * Di chuyển file từ tmp sang storage, trả về relative path
   */
  private function moveToStorage(UploadedFile $file): string
  {
    $relativDir = 'media/' . date('Y/m');
    $absoluteDir = $this->_storageRoot . '/' . $relativDir;

    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, recursive: true)) {
      throw new \RuntimeException("Không thể tạo thư mục lưu trữ: {$absoluteDir}");
    }

    $uniqueName = $this->generateFilename($file->extension);
    $relativePath = $relativDir . '/' . $uniqueName;
    $absolutePath = $this->_storageRoot . '/' . $relativePath;

    // Dùng move_uploaded_file để đảm bảo nguồn file là upload hợp lệ
    if (!move_uploaded_file($file->tmpPath, $absolutePath)) {
      throw new \RuntimeException("Không thể di chuyển file đã upload đến: {$absolutePath}");
    }

    return $relativePath;
  }

  /**
   * Tạo filename dạng UUID v4 để tránh trùng và bảo mật
   */
  private function generateFilename(string $extension): string
  {
    $bytes = random_bytes(16);

    // Format UUID v4 theo RFC 4122
    $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));

    return $uuid . '.' . $extension;
  }

  /**
   * Chuyển relative path thành absolute path
   */
  private function absolutePath(string $relativePath): string
  {
    return $this->_storageRoot . '/' . ltrim($relativePath, '/');
  }

  /**
   * Lấy Media theo ID hoặc throw exception nếu không tìm thấy
   */
  private function getOrFail(int $mediaId): Media
  {
    $media = $this->_mediaStore->findById($mediaId);

    if ($media === null) {
      throw new \RuntimeException("Media #{$mediaId} không tồn tại.");
    }

    return $media;
  }
}