<?php
namespace App\Services;

use App\Core\Files\UploadedFile;
use App\Models\Media;
use App\Stores\MediaStore;

interface IMediaService
{
  public function upload(UploadedFile $file, int $uploaderId, ?int $postId = null): Media;
  public function delete(int $mediaId): void;
  public function get(int $mediaId): ?Media;
  public function attachToPost(array $mediaIds, int $postId): void;
  public function getByPostId(int $postId): array;
  public function updateMetadata(int $mediaId, array $data): Media;
  public function deleteOrphans(\DateTimeInterface $olderThan): int;
}
class MediaService implements IMediaService
{
  private MediaStore $_mediaStore;

  public function __construct(MediaStore $menuStore)
  {
    $this->_mediaStore = $mediaStore;
  }
  public function upload(UploadedFile $file, int $uploaderId, ?int $postId = null): Media
  {
    $media = new Media(
      $post_id = $postId,
      $file_name = $file->originalName,
      $file_path = $file->tmpPath,

    );
    $this->create();

  }
  public function delete(int $mediaId): void
  {

  }
  public function get(int $mediaId): ?Media
  {
    $this->get($mediaId);
  }
  public function attachToPost(array $mediaIds, int $postId): void
  {

  }
  public function getByPostId(int $postId): array
  {

  }
  public function updateMetadata(int $mediaId, array $data): Media
  {

  }
  public function deleteOrphans(\DateTimeInterface $olderThan): int
  {

  }
}