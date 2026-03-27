<?php

namespace App\Controllers;

require_once BASE_PATH . "/includes/core/controller.php";

use App\Core\Controller;
use App\Services\{CarouselService, MenuService, WebSettingsService};

class SiteController extends Controller
{
  private MenuService $_menuService;
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
    WebSettingsService $settingService,
  ) {
    $this->_menuService = $menuService;
    $this->_carouselService = $carouselService;
    $this->_settingService = $settingService;

    $this->_loadSettings();
  }

  // ============================================================================
  // Pages
  // ============================================================================

  public function index(): void
  {
    // Sử dụng method mới: lấy thẳng menu cùng cây items của nó
    $mainMenu = $this->_menuService->getMenuByKeyWithItems('main_nav');
    $menuItemsTree = $mainMenu !== null ? $mainMenu->items : [];

    // Lấy slide carousel (Kèm fallback an toàn nếu chưa có dữ liệu)
    $carousel = $this->_carouselService->getBySlugWithSlides("landing-page");
    $carouselSlides = $carousel !== null ? $carousel->slides : [];

    $this->render('site/landing', [
      'menu' => $menuItemsTree,
      'carouselSlides' => $carouselSlides,
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