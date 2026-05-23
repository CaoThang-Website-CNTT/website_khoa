<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\MediaService;
use App\Core\RequestValidator;
use App\Core\Files\UploadedFileHandler;

class MediaController extends Controller
{
  private MediaService $_mediaService;
  private UploadedFileHandler $_fileHandler;

  public function __construct(
    MediaService $mediaService,
    UploadedFileHandler $fileHandler
  )
  {
    $this->_mediaService = $mediaService;
    $this->_fileHandler = $fileHandler;
  }

  /**
   * GET /admin/media
   * Hiển thị trang quản lý thư viện media.
   */
  public function index(Request $request)
  {
    $currentPage = (int)$request->query('page', 1);
    $limit = (int)$request->query('limit', 15);

    $data = $this->_mediaService->getMedias($currentPage, $limit);

    $this->render('admin/media/index', [
      'data' => $data,
    ], layout: 'dashboard_layout');
  }

  /**
   * GET /admin/media/create
   * Hiển thị trang thêm media.
   */
  public function create()
  {
    $this->render('admin/media/create', [], layout: 'dashboard_layout');
  }

  /**
   * GET /admin/media/{media_id}
   * Hiển thị trang edit media.
   */
  public function edit(int $media_id, Request $request)
  {
    $media = $this->_mediaService->getMediaById($media_id);
    if (!$media) {
      $request->session()->flashNotify('error', 'Media ' . $media_id . ' không tồn tại.');
      return $this->redirect('admin/media');
    }
    $this->render('admin/media/edit', ['media' => $media], layout: 'dashboard_layout');
  }

  /**
   * POST /admin/media
   * Nhận multipart/form-data, delegate hoàn toàn cho Service.
   * Controller không chạm vào $_FILES trực tiếp.
   */
  public function store(Request $request)
  {
    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'title' => ['max:255'],
      'alt_text' => ['max:255'],
    ];

    if (!$validator->validate($data, $rules)) {
        $request->flashOldInputs();
        $request->session()->flashErrors($validator->getErrors());
        return $this->redirect('admin/media/create');
    }

    // Validate file tồn tại trước khi đẩy xuống Service
    if (!$request->hasFile('file')) {
        $request->session()->flashErrors(['file' => 'Vui lòng chọn file để tải lên.']);
        return $this->redirect('admin/media/create');
    }

    try {
        $uploadedFile = $this->_fileHandler->processUpload($request->file('file'), $data['alt_text'] ?? '');
        $media = $this->_mediaService->create($uploadedFile, $data, compressMode: 'standard');

        $request->session()->flashNotify(
            'success',
            'Tạo media thành công!',
            'Media ' . $media->file_name . ' đã được tạo.'
        );
    } catch (\RuntimeException $e) {
        $request->session()->flashNotify('error', $e->getMessage());
        return $this->redirect('admin/media/create');
    }

    return $this->redirect('admin/media');
  }

  /**
   * PUT /admin/media/{media_id}
   * Chỉ cập nhật metadata (alt_text, file_name).
   * file_path là immutable — không cho phép đổi file ở đây.
   */
  public function update(int $media_id, Request $request)
  {
      $data = $request->all();

      $validator = new RequestValidator();
      $rules = [
          'title' => ['max:255'],
          'alt_text'  => ['max:255'],
      ];

      if (!$validator->validate($data, $rules)) {
          $request->flashOldInputs();
          $request->session()->flashErrors($validator->getErrors());
          return $this->redirect('admin/media/' . $media_id . '/edit');
      }

      try {
          $this->_mediaService->updateMetadata($media_id, [
              'title' => $data['title'],
              'alt_text'  => $data['alt_text'] ?? '',
          ]);

          $request->session()->flashNotify('success', 'Cập nhật media thành công!');
      } catch (\RuntimeException $e) {
          $request->session()->flashNotify('error', $e->getMessage());
          return $this->redirect('admin/media/' . $media_id . '/edit');
      }

      return $this->redirect('admin/media');
  }

  /**
   * POST /admin/media/{media_id}/destroy
   * Xóa file vật lý + record DB.
   * Dùng POST thay vì DELETE vì form HTML không hỗ trợ DELETE method.
   */
  public function destroy(Request $request, int $media_id)
  {
      try {
          $this->_mediaService->delete($media_id);
          $request->session()->flashNotify('success', 'Đã xóa media.');
      } catch (\RuntimeException $e) {
          $request->session()->flashNotify('error', $e->getMessage());
      }

      return $this->redirect('admin/media');
  }
}
