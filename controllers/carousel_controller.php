<?php
namespace App\Controllers;

require_once BASE_PATH . '/includes/core/controller.php';
require_once BASE_PATH . '/includes/core/request_validator.php';
require_once BASE_PATH . '/includes/helpers.php';
require_once BASE_PATH . '/services/carousel_service.php';

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Services\CarouselService;

class CarouselController extends Controller
{
  private CarouselService $_carouselService;

  public function __construct(CarouselService $carouselService)
  {
    $this->_carouselService = $carouselService;
  }

  public function index(Request $request)
  {
    $page = $this->_carouselService->getPaginated((int) ($request->query('page') ?? 1));
    $this->render('admin/carousels/index', ['page' => $page], layout: 'dashboard_layout');
  }

  public function create()
  {
    $this->render('admin/carousels/create', [], layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $data = $request->all();
    $validator = new Validator();
    if (!$validator->validate($data, ['name' => ['required', 'max:255'], 'slug' => ['max:255']])) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/carousels/create');
    }
    if (empty($data['slug'])) {
      $data['slug'] = generateSlug($data['name']);
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
    if (!$carousel)
      $this->abort(404);
    $this->render('admin/carousels/edit', ['carousel' => $carousel], layout: 'dashboard_layout');
  }

  public function update(string $id, Request $request)
  {
    $carousel = $this->_carouselService->getById((int) $id);
    if (!$carousel)
      $this->abort(404);
    $data = $request->all();
    $validator = new Validator();
    if (!$validator->validate($data, ['name' => ['required', 'max:255'], 'slug' => ['max:255']])) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/carousels/' . $id);
    }
    if (empty($data['slug'])) {
      $data['slug'] = generateSlug($data['name']);
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
    $carousel = $this->_carouselService->getWithSlides((int) $carouselId);
    if (!$carousel)
      $this->abort(404);
    $this->render('admin/carousels/slides/index', ['carousel' => $carousel], layout: 'dashboard_layout');
  }

  public function createSlide(string $carouselId)
  {
    $carousel = $this->_carouselService->getById((int) $carouselId);
    if (!$carousel)
      $this->abort(404);
    $this->render('admin/carousels/slides/create', ['carousel' => $carousel], layout: 'dashboard_layout');
  }

  public function storeSlide(string $carouselId, Request $request)
  {
    $data = $request->all();
    $validator = new Validator();
    if (!$validator->validate($data, ['title' => ['required', 'max:255'], 'image_path' => ['required']])) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect("admin/carousels/{$carouselId}/slides/create");
    }
    $newSlideId = $this->_carouselService->addSlide((int) $carouselId, [
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
    if (!$carousel || !$slide)
      $this->abort(404);
    $this->render('admin/carousels/slides/edit', ['carousel' => $carousel, 'slide' => $slide], layout: 'dashboard_layout');
  }

  public function updateSlide(string $carouselId, string $slideId, Request $request)
  {
    $data = $request->all();
    $validator = new Validator();
    if (!$validator->validate($data, ['title' => ['required', 'max:255'], 'image_path' => ['required']])) {
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
    $data = $request->all();
    $moveId = !empty($data['move_id']) ? (int) $data['move_id'] : null;
    $direction = $data['direction'] ?? null;
    if (!$moveId || !in_array($direction, ['up', 'down'])) {
      $request->flash('error', 'Dữ liệu sắp xếp không hợp lệ.');
      return $this->redirect('admin/carousels/' . $carouselId);
    }
    $isSuccess = $this->_carouselService->reorderSlides($moveId, $direction);
    if ($isSuccess) {
      $request->flash('success', 'Đã cập nhật thứ tự slide.');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }
    return $this->redirect('admin/carousels/' . $carouselId);
  }
}