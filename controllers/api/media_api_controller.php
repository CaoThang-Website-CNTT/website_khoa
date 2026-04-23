<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Controller;
use App\Services\MediaService;
use App\Core\Files\UploadedFileHandler;
use Exception;

class MediaApiController extends Controller
{
  private MediaService $_mediaService;
  private UploadedFileHandler $_fileHandler;
  public function __construct(
    MediaService $mediaService,
    UploadedFileHandler $fileHandler,
  ) {
    $this->_mediaService = $mediaService;
    $this->_fileHandler = $fileHandler;
  }

  public function upload(Request $request)
  {
    try {
      $altText = trim($request->input('alt_text', '')) ?: null;
      $postId = $request->input('post_id')
        ? (int) $request->input('post_id')
        : null;

      // UploadedFileHandler is the sole point of contact with $_FILES.
      // It validates structure, PHP upload error code, extension whitelist,
      // and MIME type (magic-byte sniffing) before returning a safe UploadedFile.
      $uploadedFile = $this->_fileHandler->fromGlobals('file', $altText);

      $media = $this->_mediaService->upload($uploadedFile, $postId);

      return $this->json(
        data: $media->toArray(),
        message: 'Upload thành công.',
        status: 201,
      );
    } catch (\RuntimeException $e) {
      return $this->json(data: null, message: $e->getMessage(), status: 422);
    }
  }
  public function show(Request $request, int $media_id)
  {
    $media = $this->_mediaService->get($media_id);

    if ($media === null) {
      return $this->json(data: null, message: 'Không tìm thấy media.', status: 404);
      return;
    }

    return $this->json(data: $media->toArray(), message: 'OK');
  }
  public function indexByPost(Request $request)
  {
    $postId = (int) $request->input('post_id', 0);

    if ($postId <= 0) {
      return $this->json(data: null, message: 'post_id không hợp lệ.', status: 400);
      return;
    }

    $mediaList = $this->_mediaService->getByPostId($postId);

    return $this->json(
      data: array_map([$this, 'serializeMedia'], $mediaList),
      message: 'OK',
    );
  }
  public function updateMetadata(Request $request, int $id)
  {
    try {
      $data = array_filter([
        'alt_text' => $request->input('alt_text'),
        'post_id' => $request->input('post_id') !== null
          ? (int) $request->input('post_id')
          : null,
      ], static fn($v) => $v !== null);

      if (empty($data)) {
        return $this->json(data: null, message: 'Không có trường nào để cập nhật.', status: 400);
        return;
      }

      $media = $this->_mediaService->updateMetadata($id, $data);

      return $this->json(data: $media->toArray(), message: 'Cập nhật thành công.');
    } catch (\RuntimeException $e) {
      return $this->json(data: null, message: $e->getMessage(), status: 422);
    }
  }
  public function delete(Request $request, int $media_id)
  {
    try {
      $this->_mediaService->delete($media_id);
      return $this->json(data: null, message: 'Đã xóa thành công.');
    } catch (\RuntimeException $e) {
      return $this->json(data: null, message: $e->getMessage(), status: 422);
    }
  }
  public function attachToPost(Request $request)
  {
    try {
      $mediaIds = array_map('intval', (array) $request->input('media_ids', []));
      $postId = (int) $request->input('post_id', 0);

      if (empty($mediaIds) || $postId <= 0) {
        return $this->json(data: null, message: 'media_ids và post_id là bắt buộc.', status: 400);
        return;
      }

      $this->_mediaService->attachToPost($mediaIds, $postId);

      return $this->json(data: null, message: 'Gắn media vào bài viết thành công.');
    } catch (\RuntimeException $e) {
      return $this->json(data: null, message: $e->getMessage(), status: 422);
    }
  }
  public function deleteOrphans(Request $request)
  {
    try {
      $hours = max(1, (int) $request->input('older_than_hours', 24));
      $cutoff = new \DateTime("-{$hours} hours");
      $deleted = $this->_mediaService->deleteOrphans($cutoff);

      return $this->json(
        data: ['deleted_count' => $deleted],
        message: "Đã xóa {$deleted} file mồ côi.",
      );
    } catch (\RuntimeException $e) {
      return $this->json(data: null, message: $e->getMessage(), status: 500);
    }
  }
}