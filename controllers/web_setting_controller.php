<?php

namespace App\Controllers;

require_once BASE_PATH . '/includes/core/controller.php';
require_once BASE_PATH . '/includes/core/request_validator.php';
require_once BASE_PATH . '/models/web_setting.php';

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;

use App\Models\WebSetting;

use App\Services\WebSettingsService;

class WebSettingsController extends Controller
{
  private WebSettingsService $_settingsService;

  /** Các giá trị hợp lệ cho cột ENUM `type` trong DB. */
  private const ALLOWED_TYPES = [
    // Text-based
    'string',
    'text',
    'email',
    'url',
    'json',
    'bool',
    // Number-based
    'int',
    'float',
    // Datetime-based
    'datetime',
  ];

  public function __construct(WebSettingsService $settingsService)
  {
    $this->_settingsService = $settingsService;
  }

  // ============================================================================
  // Index — danh sách group
  // ============================================================================

  /**
   * Hiển thị danh sách các nhóm setting.
   * Mỗi group hiển thị như một thẻ/row, click vào để vào trang batch-edit.
   */
  public function index(): void
  {
    $all = $this->_settingsService->getAllSettings();

    // Gom settings theo group, giữ nguyên thứ tự sort_order từ service.
    $groups = [];
    foreach ($all as $setting) {
      $groups[$setting->group][] = $setting;
    }

    $this->render('admin/web_settings/index', [
      'groups' => $groups,
    ], layout: 'dashboard_layout');
  }

  // ============================================================================
  // Create / Store — tạo setting mới
  // ============================================================================

  /**
   * Hiển thị form tạo setting mới.
   */
  public function create(): void
  {
    $this->render('admin/web_settings/create', [
      'allowedTypes' => self::ALLOWED_TYPES,
      'groups' => $this->_settingsService->getGroups(),
    ], layout: 'dashboard_layout');
  }

  /**
   * Lưu setting mới vào DB.
   *
   * Validation:
   *   - key:   bắt buộc, tối đa 120 ký tự, phải unique
   *   - group: bắt buộc, tối đa 60 ký tự
   *   - type:  bắt buộc, phải nằm trong ALLOWED_TYPES
   *   - label: bắt buộc, tối đa 150 ký tự
   */
  public function store(Request $request)
  {
    $data = $request->all();

    $validator = new Validator();
    $rules = [
      'key' => ['required', 'max:120'],
      'group' => ['required', 'max:60'],
      'type' => ['required'],
      'label' => ['required', 'max:150'],
      'description' => ['max:255'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/web_settings/create');
    }

    // Validate ENUM type
    if (!in_array($data['type'], self::ALLOWED_TYPES, strict: true)) {
      $validator->addError('type', 'Loại dữ liệu không hợp lệ.');
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/web_settings/create');
    }

    // Validate key unique
    if (!$this->_settingsService->isKeyUnique($data['key'])) {
      $validator->addError('key', 'Key này đã tồn tại, vui lòng chọn key khác.');
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/web_settings/create');
    }

    $newId = $this->_settingsService->createSetting([
      'key' => $data['key'],
      'group' => $data['group'],
      'type' => $data['type'],
      'value' => !empty($data['value']) ? $data['value'] : null,
      'default_value' => !empty($data['default_value']) ? $data['default_value'] : null,
      'label' => $data['label'],
      'description' => !empty($data['description']) ? $data['description'] : null,
      'autoload' => isset($data['autoload']) ? 1 : 0,
      'is_locked' => 0,
      'sort_order' => !empty($data['sort_order']) ? (int) $data['sort_order'] : 0,
    ]);

    if ($newId) {
      $request->flash('success', 'Tạo setting thành công!');
      return $this->redirect('admin/web_settings');
    }

    $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    return $this->redirect('admin/web_settings/create');
  }

  // ============================================================================
  // Edit / Batch Update — chỉnh sửa toàn bộ settings trong một group
  // ============================================================================

  /**
   * Hiển thị form batch-edit cho một group.
   *
   * Template nhận $settings (WebSetting[]) và render input phù hợp
   * theo $setting->type cho từng row:
   *   - bool         → <input type="hidden" name="settings[{id}][value]" value="0">
   *                    <input type="checkbox" name="settings[{id}][value]" value="1">
   *   - json / text  → <textarea name="settings[{id}][value]">
   *   - còn lại      → <input type="text" name="settings[{id}][value]">
   *
   * Lưu ý naming convention:
   * @param string $group Tên group, VD: 'general', 'homepage'
   */
  public function edit(string $group): void
  {
    $settings = $this->_settingsService->getByGroup($group);

    if (empty($settings)) {
      $this->abort(404);
    }

    $this->render('admin/web_settings/edit', [
      'group' => $group,
      'settings' => $settings,
    ], layout: 'dashboard_layout');
  }

  /**
   * Batch-save toàn bộ settings trong một group.
   *
   * Cấu trúc POST kỳ vọng từ form:
   *   settings[{id}][value] → giá trị của setting
   *
   * Luồng xử lý cho mỗi setting:
   *   1. Bỏ qua nếu is_locked.
   *   2. Normalize bool (unchecked checkbox → '0').
   *   3. Validate value theo type (email, url, int, float, bool, json, datetime).
   *   4. Gọi updateSetting() với toàn bộ data hiện tại, chỉ thay value.
   *
   * @param string  $group   Tên group cần batch-save.
   * @param Request $request
   */
  public function batchUpdate(string $group, Request $request)
  {
    $settings = $this->_settingsService->getByGroup($group);

    if (empty($settings)) {
      $this->abort(404);
    }

    // settings[{id}][value] từ POST body
    $submittedValues = $request->input('settings', []);
    $errors = [];

    foreach ($settings as $setting) {
      // is_locked: service cũng chặn ở tầng dưới, nhưng skip sớm ở đây
      // để không sinh ra lỗi validation cho những field admin không được chỉnh sửa.
      if ($setting->is_locked)
        continue;

      // ── Xử lý value từ POST ──────────────────────────────────────────────
      $newValue = $submittedValues[$setting->id]['value'] ?? null;

      // bool đến từ checkbox — nếu unchecked thì key không có trong POST.
      // Template cần có hidden input trước checkbox để đảm bảo '0' luôn được gửi.
      // Normalize lại ở đây để chắc chắn giá trị luôn là '0' hoặc '1'.
      if ($setting->type === 'bool') {
        $newValue = $newValue ? '1' : '0';
      }

      // ── Validate value theo type ──────────────────────────────────────────
      $error = $this->_settingsService->validateByType($setting, $newValue);
      if ($error !== null) {
        $errors[$setting->id] = [$error];
        continue;
      }

      $this->_settingsService->updateSetting(
        $setting->id,
        $this->buildUpdatePayload($setting, $newValue),
      );
    }

    if (!empty($errors)) {
      $request->flashErrors($errors);
      $request->flash('error', 'Một số cài đặt không hợp lệ, vui lòng kiểm tra lại.');
    } else {
      $request->flash('success', 'Cập nhật cài đặt thành công!');
    }

    return $this->redirect('admin/web_settings/' . $group . '/edit');
  }

  // ============================================================================
  // Destroy — xóa một setting
  // ============================================================================

  /**
   * Xóa cứng một setting theo ID.
   * Sau khi xóa, redirect về trang edit của group mà setting đó thuộc về.
   *
   * is_locked được kiểm tra tại đây để flash thông báo rõ ràng cho admin
   * trước khi service từ chối — nhất quán với cách MenuController kiểm tra isEditable().
   *
   * @param string  $id      ID của setting cần xóa (route param dạng string).
   * @param Request $request
   */
  public function destroy(string $id, Request $request)
  {
    $setting = $this->_settingsService->getById((int) $id);

    if (!$setting) {
      $this->abort(404);
    }

    if ($setting->is_locked) {
      $request->flash('error', 'Setting này do hệ thống định nghĩa, không thể xóa.');
      return $this->redirect('admin/web_settings/' . $setting->group . '/edit');
    }

    $isSuccess = $this->_settingsService->deleteSetting((int) $id);

    if ($isSuccess) {
      $request->flash('success', 'Xóa setting thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/web_settings/' . $setting->group . '/edit');
  }

  // ============================================================================
  // Helpers
  // ============================================================================

  /**
   * Xây dựng payload đầy đủ cho updateSetting().
   *
   * updateSetting() là full-row update nên cần truyền toàn bộ field.
   * Method này spread tất cả field hiện tại từ $setting và chỉ override `value`.
   *
   * @param  WebSetting $setting  Setting hiện tại — nguồn cho các field không thay đổi.
   * @param  mixed      $newValue Giá trị mới sẽ được lưu vào cột `value`.
   * @return array
   */
  private function buildUpdatePayload(WebSetting $setting, mixed $newValue): array
  {
    return [
      'key' => $setting->key,
      'group' => $setting->group,
      'type' => $setting->type,
      'value' => $newValue,
      'default_value' => $setting->default_value,
      'label' => $setting->label,
      'description' => $setting->description,
      'autoload' => $setting->autoload ? 1 : 0,
      'sort_order' => $setting->sort_order,
      'updated_by' => $this->currentAdminId(),
    ];
  }

  /**
   * Lấy ID của admin đang đăng nhập từ session.
   * Trả về null nếu chưa đăng nhập — service sẽ lưu NULL vào updated_by.
   *
   * Thay 'admin_id' bằng session key thực tế của dự án nếu khác.
   */
  private function currentAdminId(): ?int
  {
    return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
  }

}