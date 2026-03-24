<?php

namespace App\Controllers;

require_once BASE_PATH . "/includes/core/controller.php";
require_once BASE_PATH . '/includes/core/request_validator.php';
require_once BASE_PATH . '/models/carousel.php';
require_once BASE_PATH . '/models/carousel_slide.php';

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Services\CarouselService;

class CarouselController extends Controller
{
  private $_carouselService;

  public function __construct(CarouselService $carouselService)
  {
    $this->_carouselService = $carouselService;
  }
  public function index()
  {
    $carousels = $this->_carouselService->getAll();
    $this->render("admin/carousels/index", [
      "carousels" => $carousels
    ], layout: 'dashboard_layout');
  }

  public function create()
  {
    $this->render("admin/carousels/create", [], layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $data = $request->all();
    $validator = new Validator();

    $rules = [
      'name' => ['required', 'max:255'],
      'slug' => ['max:255'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/carousels/create');
    }

    // Auto-generate slug nếu để trống
    if (empty($data['slug'])) {
      $data['slug'] = $this->generateSlug($data['name']);
    }

    if (!$this->_carouselService->isSlugUnique($data['slug'])) {
      $validator->addError('slug', 'Slug này đã tồn tại, vui lòng chọn slug khác.');
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/carousels/create');
    }

    $newId = $this->_carouselService->create([
      'name' => $data['name'],
      'slug' => $data['slug'],
      'is_active' => !empty($data['is_active']) ? 1 : 0,
    ]);

    if ($newId) {
      $request->flash('success', 'Tạo carousel thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/carousels/create');
  }

  public function edit(string $id)
  {
    $carousel = $this->_carouselService->getById((int) $id);

    if (!$carousel) {
      die("Không tìm thấy carousel với id: $id");
    }

    $this->render("admin/carousels/edit", [
      "carousel" => $carousel
    ], layout: 'dashboard_layout');
  }

  public function update(string $id, Request $request)
  {
    $data = $request->all();
    $carousel = $this->_carouselService->getById((int) $id);

    if (!$carousel) {
      die("Không tìm thấy carousel với id: $id");
    }

    $validator = new Validator();
    $rules = [
      'name' => ['required', 'max:255'],
      'slug' => ['max:255'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/carousels/' . $id);
    }

    if (empty($data['slug'])) {
      $data['slug'] = $this->generateSlug($data['name']);
    }

    if (!$this->_carouselService->isSlugUnique($data['slug'], (int) $id)) {
      $validator->addError('slug', 'Slug này đã tồn tại, vui lòng chọn slug khác.');
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/carousels/' . $id);
    }

    $isSuccess = $this->_carouselService->update((int) $id, [
      'name' => $data['name'],
      'slug' => $data['slug'],
      'is_active' => !empty($data['is_active']) ? 1 : 0,
    ]);

    if ($isSuccess) {
      $request->flash('success', 'Cập nhật carousel thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/carousels/' . $id);
  }

  public function destroy(string $id, Request $request)
  {
    $isSuccess = $this->_carouselService->delete((int) $id);

    if ($isSuccess) {
      $request->flash('success', 'Xoá carousel thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/carousels');
  }
  public function slides(string $carouselId)
  {
    $carousel = $this->_carouselService->getById((int) $carouselId);

    if (!$carousel) {
      die("Không tìm thấy carousel với id: $carouselId");
    }

    $this->render("admin/carousels/slides/index", [
      "carousel" => $carousel
    ], layout: 'dashboard_layout');
  }

  public function createSlide(string $carouselId)
  {
    $carousel = $this->_carouselService->getById((int) $carouselId);

    if (!$carousel) {
      die("Không tìm thấy carousel với id: $carouselId");
    }

    $this->render("admin/carousels/slides/create", [
      "carousel" => $carousel
    ], layout: 'dashboard_layout');
  }

  public function storeSlide(string $carouselId, Request $request)
  {
    $data = $request->all();
    $validator = new Validator();

    $rules = [
      'title' => ['required', 'max:255'],
      'image_path' => ['required'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect("admin/carousels/{$carouselId}/slides/create");
    }

    $newSlideId = $this->_carouselService->createSlide([
      'carousel_id' => (int) $carouselId,
      'title' => $data['title'],
      'title_highlight' => $data['title_highlight'] ?? null,
      'description' => $data['description'] ?? null,
      'image_path' => $data['image_path'],
      'image_alt' => $data['image_alt'] ?? '',
      'cta_label' => $data['cta_label'] ?? null,
      'cta_url' => $data['cta_url'] ?? null,
      'cta_variant' => $data['cta_variant'] ?? 'primary',
      'custom_html' => $data['custom_html'] ?? null,
      'use_custom_html' => !empty($data['use_custom_html']) ? 1 : 0,
      'is_active' => !empty($data['is_active']) ? 1 : 0,
      'sort_order' => 0,
    ]);

    if ($newSlideId) {
      $request->flash('success', 'Thêm slide thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect("admin/carousels/{$carouselId}/slides");
  }

  public function editSlide(string $carouselId, string $slideId)
  {
    $carousel = $this->_carouselService->getById((int) $carouselId);
    $slide = $this->_carouselService->getSlideById((int) $slideId);

    if (!$carousel || !$slide) {
      die("Không tìm thấy dữ liệu yêu cầu.");
    }

    $this->render("admin/carousels/slides/edit", [
      "carousel" => $carousel,
      "slide" => $slide
    ], layout: 'dashboard_layout');
  }

  public function updateSlide(string $carouselId, string $slideId, Request $request)
  {
    $data = $request->all();
    $validator = new Validator();

    $rules = [
      'title' => ['required', 'max:255'],
      'image_path' => ['required'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect("admin/carousels/{$carouselId}/slides/{$slideId}");
    }

    $slide = $this->_carouselService->getSlideById((int) $slideId);

    $isSuccess = $this->_carouselService->updateSlide((int) $slideId, [
      'title' => $data['title'],
      'title_highlight' => $data['title_highlight'] ?? null,
      'description' => $data['description'] ?? null,
      'image_path' => $data['image_path'],
      'image_alt' => $data['image_alt'] ?? '',
      'cta_label' => $data['cta_label'] ?? null,
      'cta_url' => $data['cta_url'] ?? null,
      'cta_variant' => $data['cta_variant'] ?? 'primary',
      'custom_html' => $data['custom_html'] ?? null,
      'use_custom_html' => !empty($data['use_custom_html']) ? 1 : 0,
      'is_active' => !empty($data['is_active']) ? 1 : 0,
      'sort_order' => $request->input('sort_order', $slide->sort_order),
    ]);

    if ($isSuccess) {
      $request->flash('success', 'Cập nhật slide thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect("admin/carousels/{$carouselId}/slides/{$slideId}");
  }

  public function destroySlide(string $carouselId, string $slideId, Request $request)
  {
    $isSuccess = $this->_carouselService->deleteSlide((int) $slideId);

    if ($isSuccess) {
      $request->flash('success', 'Xoá slide thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect("admin/carousels/{$carouselId}");
  }

  public function reorder(string $carouselId, Request $request)
  {
    $orderedIds = $request->input('orderedIds');

    if (!is_array($orderedIds) || empty($orderedIds)) {
      $request->flash('error', 'Dữ liệu sắp xếp không hợp lệ.');
      return $this->redirect("admin/carousels/{$carouselId}/slides");
    }

    if ($this->_carouselService->reorderSlides($orderedIds)) {
      $request->flash('success', 'Đã cập nhật thứ tự slide.');
    } else {
      $request->flash('error', 'Có lỗi xảy ra khi sắp xếp.');
    }

    return $this->redirect("admin/carousels/{$carouselId}/slides");
  }
  private function generateSlug(string $name): string
  {
    return strtolower(
      preg_replace(
        '/\s+/',
        '-',
        preg_replace(
          '/[^a-z0-9\s-]/',
          '',
          str_replace(
            'đ',
            'd',
            iconv(
              'UTF-8',
              'ASCII//TRANSLIT',
              mb_strtolower(trim($name))
            )
          )
        )
      )
    );
  }
}