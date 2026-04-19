<?php
namespace App\Services;

use App\Core\Files\UploadedFile;
use App\Models\Media;

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

}