<?php
namespace App\Controllers;

require_once BASE_PATH . '/includes/core/controller.php';
require_once BASE_PATH . '/includes/core/request_validator.php';

use App\Core\Controller;
use App\Services\{GoogleOAuthService, WebSettingsService};

class AuthController extends Controller
{
  private GoogleOAuthService $_oauthService;
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
  private const PRELOAD_GROUPS = ['general', 'contact', 'social'];

  public function __construct(
    GoogleOAuthService $oauthService,
    WebSettingsService $settingService,
  ) {
    $this->_oauthService = $oauthService;
    $this->_settingService = $settingService;

    $this->_loadSettings();

  }
  public function show()
  {
    $loginUrl = $this->_oauthService->getAuthUrl();

    return $this->render('auth/login', [
      'loginUrl' => $loginUrl,
      'settings' => $this->_settings,
    ], "auth_layout");
  }
  public function googleOAuthCallback()
  {

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