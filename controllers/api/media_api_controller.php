<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Controller;
use App\Services\MediaService;
use App\Core\RequestValidator;
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

  /**
   * GET /api/v1/media
   * Trả về danh sách media phân trang. Hỗ trợ query params: page, per_page, search.
   */
  public function index(Request $request)
  {
    $page = (int)$request->query('page', 1);
    $perPage = (int)$request->query('limit', 15);
    $search = trim($request->query('search', '')) ?: null;

    
    try {
      $pageable = $this->_mediaService->getMedias($page, $perPage, $search);
      return $this->json($pageable, 200);
    } catch (Exception $e) {
      error_log('Lỗi lấy dữ liệu media: ' . $e->getMessage());
      return $this->json(['message' => 'Không tìm thấy dữ liệu yêu cầu.'], 404);
    }
  }

  /**
   * POST /api/v1/media
   * Upload file (multipart) hoặc tải từ URL.
   * Body params:
   *   - file: file nhị phân (multipart) — hoặc —
   *   - url: string URL ảnh từ Internet
   *   - alt_text: string (optional)
   *   - post_id: int (optional)
   *   - compress_mode: 'lossless'|'thumbnail'|'standard'|'banner' (optional, default 'standard')
   */
  public function upload(Request $request)
  {
    try {
      $data = $request->all();

      $validator = new RequestValidator();
      $rules = [
        'title' => ['max:255'],
        'alt_text' => ['max:255'],
      ];

      $validator->validate($data, $rules);

      if (!$request->hasFile('file')) {
        $validator->addError("file", "Vui lòng chọn file để tải lên.");
      }

      if ($validator->hasErrors()) {
        throw new \Exception(
          implode("\n", array_map(
            fn($k, $v) => "{$k}: {$v}", 
            array_keys($validator->getErrors()), 
            $validator->getErrors()
          )) . "\n"
        );
      }

      $uploadedFile = $this->_fileHandler->processUpload($request->file('file'), $data['alt_text'] ?? '');
        $media = $this->_mediaService->create($uploadedFile, $data, compressMode: 'standard');

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
    $media = $this->_mediaService->getMediaById($media_id);

    if ($media === null) {
      return $this->json(data: null, message: 'Không tìm thấy media.', status: 404);
    }

    return $this->json(data: $media->toArray(), message: 'OK');
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
}