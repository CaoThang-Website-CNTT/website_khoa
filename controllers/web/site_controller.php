<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\{PostService, CarouselService, MenuService, WebSettingsService};
use App\Editor\BlockRenderer;
use App\Models\Menu;

class SiteController extends Controller
{
  private MenuService $_menuService;
  private PostService $_postService;
  private CarouselService $_carouselService;
  private WebSettingsService $_settingService;

  /**
   * Header Menu
   */
  private ?Menu $_headerMenu = null;

  /**
   * Settings được load một lần tại constructor và tái sử dụng cho mọi method.
   * Key là setting key, value là cast_value đã được service xử lý.
   *
   * Chỉ load các group cần thiết cho public site: general, contact, seo, social.
   * Đây là substitute cho cache - khi có cache layer thật thì
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

    $this->_loadHeaderMenu();
    $this->_loadSettings();
  }

  public function index()
  {

    // Lấy slide carousel (Kèm fallback an toàn nếu chưa có dữ liệu)
    $carousel = $this->_carouselService->getBySlugWithSlides("landing-page", with_media: true);
    $carouselSlides = $carousel !== null ? $carousel->slides : [];

    $featuredNews = $this->_postService->getFeaturedPosts(4, true, ['status' => 'published']);

    // Lấy 3 bài viết mới nhất không phải bài nổi bật
    $allNewsPageable = $this->_postService->getPosts(1, 3, true, [
      'status' => 'published',
      'is_featured' => "0"
    ]);
    $latestNewsItems = $allNewsPageable->getItems();

    return $this->render('site/landing', [
      'headerMenu' => $this->_headerMenu->items,
      'carouselSlides' => $carouselSlides,
      'featuredNews' => $featuredNews,
      'latestNewsItems' => $latestNewsItems,
      'settings' => $this->_settings,
    ], "site_layout");
  }

  public function news_index()
  {

    $featuredNews = $this->_postService->getFeaturedPosts(2, true, ['status' => 'published']);

    $allNews = $this->_postService->getPosts(1, 6, true, [
      'status' => 'published',
      'is_featured' => "0"
    ]);

    return $this->render('site/news/index', [
      'headerMenu' => $this->_headerMenu->items,
      'featuredNews' => $featuredNews,
      'allNews' => $allNews,
      'settings' => $this->_settings,
    ], "site_layout");
  }

  public function news_show($slug)
  {
    $news = $this->_postService->getPostBySlug($slug, with_author: true);
    $result = BlockRenderer::compile($news->content_json);

    return $this->render('site/news/detail', [
      'headerMenu' => $this->_headerMenu->items,
      'news' => $news,
      'newsSettings' => json_decode($news->settings_json ?? '{}', true)['settings'] ?? [],
      'detail' => $result,
      'settings' => $this->_settings,
    ], "site_layout");
  }

  public function about()
  {
    return $this->render('site/about', [
      'headerMenu' => $this->_headerMenu->items,
      'settings' => $this->_settings,
    ], "site_layout");
  }

  // ============================================================================
  // Private helpers
  // ============================================================================

  /**
   * Load Header Menu
   */
  private function _loadHeaderMenu(): void
  {
    $this->_headerMenu = $this->_menuService->getMenuByKeyWithItems('header_menu');
  }

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
