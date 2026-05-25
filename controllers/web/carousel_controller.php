<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
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
    $currentPage = (int)$request->query('page', 1);
    $limit = (int)$request->query('limit', 15);

    $data = $this->_carouselService->getCarousels($currentPage, $limit);

    $this->render('admin/carousels/index', [
      'data' => $data
    ], layout: 'dashboard_layout');
  }

  public function create()
  {
    $this->render('admin/carousels/create', [], layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'name' => ['required', 'max:255'],
      'slug' => ['max:255'],
      'is_active' => [],
      'slides' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/carousels/create');
    }

    $rawSlides = is_array($data['slides'] ?? null) ? $data['slides'] : [];
    foreach ($rawSlides as $i => $slide) {
      if (empty($slide['title'])) {
        $validator->addError("slides.{$i}.title", "Slide " . ($i + 1) . ": tiêu đề không được để trống.");
      }
      if (empty($slide['media_id'])) {
        $validator->addError("slides.{$i}.media_id", "Slide " . ($i + 1) . ": ảnh không được để trống.");
      }
    }

    $slug = trim($data['slug'] ?? '');
    if ($slug !== '' && !$this->_carouselService->isSlugUnique($slug)) {
      $validator->addError('slug', 'Slug này đã tồn tại, vui lòng chọn slug khác.');
    }

    if ($validator->hasErrors()) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/carousels/create');
    }

    try {
      $newCarousel = $this->_carouselService->create([
        'name' => $data['name'],
        'slug' => $slug,
        'is_active' => !empty($data['is_active']) ? 1 : 0,
        'slides' => $rawSlides,
      ]);

      $slideCount = count($newCarousel->slides ?? []);
      $request->session()->flashNotify(
        'success',
        'Tạo carousel thành công!',
        "Carousel \"{$newCarousel->name}\" đã được tạo" . ($slideCount ? " với {$slideCount} slide." : '.')
      );
      return $this->redirect('admin/carousels');
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
      return $this->redirect('admin/carousels/create');
    }
  }

  public function edit($id)
  {
    $carousel = $this->_carouselService->getWithSlides($id);

    if (!$carousel)
      $this->abort(404);

    $this->render('admin/carousels/edit', [
      'carousel' => $carousel
    ], layout: 'dashboard_layout');
  }

  public function update($id, Request $request)
  {
    $carousel = $this->_carouselService->getCarouselById($id);

    if (!$carousel) {
      $this->abort(404);
    }

    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'name' => ['required', 'max:255'],
      'slug' => ['max:255'],
      'reorder' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/carousels/' . $id);
    }

    if (empty($data['slug'])) {
      $data['slug'] = generateSlug($data['name']);
    }

    if (!$this->_carouselService->isSlugUnique($data['slug'], (int) $id)) {
      $validator->addError('slug', 'Slug này đã tồn tại, vui lòng chọn slug khác.');
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/carousels/' . $id);
    }

    try {
      $this->_carouselService->update((int) $id, [
        'name' => $data['name'],
        'slug' => $data['slug'],
        'is_active' => !empty($data['is_active']) ? 1 : 0,
        'reorder' => $data['reorder'] ?? null,
      ]);
      $request->session()->flashNotify('success', 'Cập nhật carousel thành công!');
      return $this->redirect('admin/carousels/' . $id);
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
      return $this->redirect('admin/carousels/' . $id);
    }
  }

  public function destroy(string $id, Request $request)
  {
    $carousel = $this->_carouselService->getCarouselById((int) $id);

    if (!$carousel) {
      $this->abort(404);
    }

    try {
      $this->_carouselService->delete((int) $id);
      $request->session()->flashNotify('success', 'Xóa carousel thành công!');
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
    }

    return $this->redirect('admin/carousels');
  }

  public function slides(string $carousel_id)
  {
    $carousel = $this->_carouselService->getWithSlides((int) $carousel_id);
    if (!$carousel)
      $this->abort(404);
    $this->render('admin/carousels/slides/index', ['carousel' => $carousel], layout: 'dashboard_layout');
  }

  public function createSlide(string $carousel_id)
  {
    $carousel = $this->_carouselService->getCarouselById((int) $carousel_id);
    if (!$carousel)
      $this->abort(404);
    $this->render('admin/carousels/slides/create', ['carousel' => $carousel], layout: 'dashboard_layout');
  }

  public function storeSlide(string $carousel_id, Request $request)
  {
    $carousel = $this->_carouselService->getCarouselById((int) $carousel_id);

    if (!$carousel) {
      $this->abort(404);
    }

    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'title' => ['required', 'max:255'],
      'media_id' => ['required'],
      'title_highlight' => ['max:255'],
      'description' => [],
      'cta_label' => ['max:255'],
      'cta_url' => ['max:500'],
      'cta_variant' => [],
      'custom_html' => [],
      'use_custom_html' => [],
      'is_active' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect("admin/carousels/{$carousel_id}");
    }

    try {
      $newSlideId = $this->_carouselService->addSlide((int) $carousel_id, [
        'title' => $data['title'],
        'title_highlight' => $data['title_highlight'] ?? null,
        'description' => $data['description'] ?? null,
        'media_id' => (int) $data['media_id'],
        'cta_label' => $data['cta_label'] ?? null,
        'cta_url' => $data['cta_url'] ?? null,
        'cta_variant' => $data['cta_variant'] ?? 'primary',
        'custom_html' => $data['custom_html'] ?? null,
        'use_custom_html' => !empty($data['use_custom_html']) ? 1 : 0,
        'is_active' => !empty($data['is_active']) ? 1 : 0,
        'sort_order' => 0,
      ]);

      $request->session()->flashNotify('success', 'Thêm slide thành công!');
      return $this->redirect("admin/carousels/{$carousel_id}");
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
      return $this->redirect("admin/carousels/{$carousel_id}");
    }
  }

  public function editSlide(string $carousel_id, string $slide_id)
  {
    $carousel = $this->_carouselService->getCarouselById((int) $carousel_id);
    $slide = $this->_carouselService->getSlideById((int) $slide_id);
    if (!$carousel || !$slide)
      $this->abort(404);
    $this->render('admin/carousels/slides/edit', ['carousel' => $carousel, 'slide' => $slide], layout: 'dashboard_layout');
  }

  public function updateSlide(string $slide_id, Request $request)
  {
    $slide = $this->_carouselService->getSlideById((int) $slide_id);

    if (!$slide) {
      $this->abort(404);
    }

    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'title' => ['required', 'max:255'],
      'media_id' => ['required'],
      'title_highlight' => ['max:255'],
      'description' => [],
      'cta_label' => ['max:255'],
      'cta_url' => ['max:500'],
      'cta_variant' => [],
      'custom_html' => [],
      'use_custom_html' => [],
      'is_active' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect("admin/carousels/". $slide->carousel_id);
    }

    try {
      $this->_carouselService->updateSlide((int) $slide_id, [
        'title' => $data['title'],
        'title_highlight' => $data['title_highlight'] ?? null,
        'description' => $data['description'] ?? null,
        'media_id' => (int) $data['media_id'],
        'cta_label' => $data['cta_label'] ?? null,
        'cta_url' => $data['cta_url'] ?? null,
        'cta_variant' => $data['cta_variant'] ?? 'primary',
        'custom_html' => $data['custom_html'] ?? null,
        'use_custom_html' => !empty($data['use_custom_html']) ? 1 : 0,
        'is_active' => !empty($data['is_active']) ? 1 : 0,
        'sort_order' => $request->input('sort_order', $slide->sort_order),
      ]);
      $request->session()->flashNotify('success', 'Cập nhật slide thành công!');
      return $this->redirect("admin/carousels/". $slide->carousel_id);
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
      return $this->redirect("admin/carousels/". $slide->carousel_id);
    }
  }

  public function destroySlide(string $carousel_id, string $slide_id, Request $request)
  {
    $slide = $this->_carouselService->getSlideById((int) $slide_id);

    if (!$slide) {
      $this->abort(404);
    }

    try {
      $this->_carouselService->deleteSlide((int) $slide_id);
      $request->session()->flashNotify('success', 'Xóa slide thành công!');
    } catch (\Exception $e) {
      $request->session()->flashNotify(
        'error',
        'Có lỗi xảy ra, vui lòng thử lại.',
        $e->getMessage()
      );
    }

    return $this->redirect("admin/carousels/{$carousel_id}");
  }
}