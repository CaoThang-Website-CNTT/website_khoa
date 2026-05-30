<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\{PostService, CarouselService, MenuService, WebSettingsService};

class SiteController extends Controller
{
  private MenuService $_menuService;
  private PostService $_postService;
  private CarouselService $_carouselService;
  private WebSettingsService $_settingService;

  /**
   * Settings được load một lần tại constructor và tái sử dụng cho mọi method.
   * Key là setting key, value là cast_value đã được service xử lý.
   *
   * Chỉ load các group cần thiết cho public site: general, contact, seo, social.
   * Đây là substitute cho cache — khi có cache layer thật thì
   * chỉ cần thay getByGroup() bằng getAutoloaded() ở service layer.
   *
   * @var array<string, mixed>
   */
  private array $_settings = [];

  /**
   * Groups cần load cho public site.
   * Thêm/bớt group tại đây khi site mở rộng.
   */
  private const PRELOAD_GROUPS = ['general', 'contact', 'seo', 'social'];

  public function __construct(
    MenuService $menuService,
    CarouselService $carouselService,
    PostService $postService,
    WebSettingsService $settingService,
  ) {
    $this->_menuService = $menuService;
    $this->_carouselService = $carouselService;
    $this->_postService = $postService;
    $this->_settingService = $settingService;

    $this->_loadSettings();
  }

  public function index()
  {
    $headerMenu = $this->_menuService->getMenuByKeyWithItems('header_menu');
    $headerMenuItems = $headerMenu !== null ? $headerMenu->items : [];

    // Lấy slide carousel (Kèm fallback an toàn nếu chưa có dữ liệu)
    $carousel = $this->_carouselService->getBySlugWithSlides("landing-page", with_media: true);
    $carouselSlides = $carousel !== null ? $carousel->slides : [];

    return $this->render('site/landing', [
      'headerMenu' => $headerMenuItems,
      'carouselSlides' => $carouselSlides,
      'settings' => $this->_settings,
    ], "site_layout");
  }

  public function news_index()
  {
    $headerMenu = $this->_menuService->getMenuByKeyWithItems('header_menu');
    $headerMenuItems = $headerMenu !== null ? $headerMenu->items : [];
    $featuredNews = $this->_postService->getFeaturedPosts(2);
    $allNews = $this->_postService->getPosts(page: 1, limit: 6);

    return $this->render('site/news/index', [
      'headerMenu' => $headerMenuItems,
      'featuredNews' => $featuredNews,
      'allNews' => $allNews,
      'settings' => $this->_settings,
    ], "site_layout");
  }

  public function news_show($slug) {
    $headerMenu = $this->_menuService->getMenuByKeyWithItems('header_menu');
    $headerMenuItems = $headerMenu !== null ? $headerMenu->items : [];
    $news = $this->_postService->getPostBySlug($slug);

    return $this->render('site/news/detail', [
      'headerMenu' => $headerMenuItems,
      'news' => $news,
      'settings' => $this->_settings,
    ], "site_layout");
  }

  // ============================================================================
  // Private helpers
  // ============================================================================

  /**
   * Load tất cả settings thuộc PRELOAD_GROUPS vào $_settings.
   * Kết quả là flat map: key → cast_value.
   */
  private function _loadSettings(): void
  {
    foreach (self::PRELOAD_GROUPS as $group) {
      $rows = $this->_settingService->getByGroup($group);
      foreach ($rows as $setting) {
        $this->_settings[$setting->key] = $setting->cast_value;
      }
    }
  }
}