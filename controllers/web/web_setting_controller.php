<?php
namespace App\Controllers;

require_once BASE_PATH . '/includes/core/controller.php';
require_once BASE_PATH . '/includes/core/request_validator.php';
require_once BASE_PATH . '/services/web_setting_service.php';

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\WebSetting;
use App\Services\WebSettingsService;
class WebSettingsController extends Controller
{
  private WebSettingsService $_settingsService;
  private const ALLOWED_TYPES = ['string', 'text', 'email', 'url', 'json', 'bool', 'int', 'float', 'datetime'];
  public function __construct(WebSettingsService $settingsService)
  {
    $this->_settingsService = $settingsService;
  }
  public function index()
  {
    $groups = $this->_settingsService->getGroupsWithSettings();
    $this->render('admin/web_settings/index', ['data' => $groups], layout: 'dashboard_layout');
  }
  public function create()
  {
    $this->render('admin/web_settings/create', [
      'allowedTypes' => self::ALLOWED_TYPES,
      'groups' => $this->_settingsService->getAllGroups(),
    ], layout: 'dashboard_layout');
  }
  public function store(Request $request)
  {
    $data = $request->all();
    $validator = new Validator();
    if (
      !$validator->validate($data, [
        'key' => ['required', 'max:120'],
        'group' => ['required', 'max:60'],
        'type' => ['required'],
        'label' => ['required', 'max:150'],
        'description' => ['max:255'],
      ])
    ) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/web_settings/create');
    }
    if (!in_array($data['type'], self::ALLOWED_TYPES, strict: true)) {
      $validator->addError('type', 'Loại dữ liệu không hợp lệ.');
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/web_settings/create');
    }
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
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
      return $this->redirect('admin/web_settings/create');
    }
  }
  public function edit(string $group)
  {
    $settings = $this->_settingsService->getByGroup($group);
    if (empty($settings))
      $this->abort(404);
    $this->render('admin/web_settings/edit', ['group' => $group, 'settings' => $settings], layout: 'dashboard_layout');
  }
  public function batchUpdate(string $group, Request $request)
  {
    $settings = $this->_settingsService->getByGroup($group);
    if (empty($settings))
      $this->abort(404);
    $submittedValues = $request->input('settings', []);
    $errors = [];
    foreach ($settings as $setting) {
      if ($setting->is_locked)
        continue;
      $newValue = $submittedValues[$setting->id]['value'] ?? null;
      if ($setting->type === 'bool')
        $newValue = $newValue ? '1' : '0';
      $error = $this->_settingsService->validateByType($setting, $newValue);
      if ($error !== null) {
        $errors[$setting->id] = [$error];
        continue;
      }
      $this->_settingsService->updateSetting($setting->id, $this->_buildUpdatePayload($setting, $newValue));
    }
    if (!empty($errors)) {
      $request->flashErrors($errors);
      $request->flash('error', 'Một số cài đặt không hợp lệ, vui lòng kiểm tra lại.');
    } else {
      $request->flash('success', 'Cập nhật cài đặt thành công!');
    }
    return $this->redirect('admin/web_settings/' . $group . '/edit');
  }
  public function destroy(string $id, Request $request)
  {
    $setting = $this->_settingsService->getById((int) $id);
    if (!$setting)
      $this->abort(404);
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
  private function _buildUpdatePayload(WebSetting $setting, mixed $newValue): array
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
      'updated_by' => isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null,
    ];
  }
}