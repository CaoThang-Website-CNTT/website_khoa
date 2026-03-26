<?php
namespace App\Services;

require_once BASE_PATH . '/stores/web_setting_store.php';
require_once BASE_PATH . '/models/web_setting.php';

use App\Stores\WebSettingsStore;
use App\Models\WebSetting;

interface IWebSettingsService
{
  /** @return WebSetting[] */
  public function getAllSettings(): array;
  /** @return WebSetting[] */
  public function getByGroup(string $group): array;
  /** @return WebSetting[] */
  public function getAutoloaded(): array;
  public function getById(int $id): ?WebSetting;
  public function getByKey(string $key): ?WebSetting;
  public function getValue(string $key, mixed $fallback = null): mixed;
  public function createSetting(array $data): int;
  public function updateSetting(int $id, array $data): bool;
  public function deleteSetting(int $id): bool;
  public function isKeyUnique(string $key, ?int $excludeId = null): bool;
  public function getAllGroups(?int $pageTo = null, ?int $limit = null): array;
  public function getTotalGroupsCount(): int;
  public function validateByType(WebSetting $setting, mixed $value): ?string;
}

class WebSettingsService implements IWebSettingsService
{
  private WebSettingsStore $_store;
  public function __construct(WebSettingsStore $store)
  {
    $this->_store = $store;
  }
  /** @return WebSetting[] */
  public function getAllSettings(): array
  {
    return array_map(fn($s) => $this->hydrate($s), $this->_store->getAllSettings());
  }
  /** @return WebSetting[] */
  public function getByGroup(string $group): array
  {
    return array_map(fn($s) => $this->hydrate($s), $this->_store->getByGroup($group));
  }
  /** @return WebSetting[] */
  public function getAutoloaded(): array
  {
    return array_map(fn($s) => $this->hydrate($s), $this->_store->getAutoloaded());
  }
  public function getById(int $id): ?WebSetting
  {
    $setting = $this->_store->getById($id);
    return $setting ? $this->hydrate($setting) : null;
  }
  public function getByKey(string $key): ?WebSetting
  {
    $setting = $this->_store->getByKey($key);
    return $setting ? $this->hydrate($setting) : null;
  }
  public function getValue(string $key, mixed $fallback = null): mixed
  {
    $setting = $this->getByKey($key);
    return $setting ? $setting->cast_value : $fallback;
  }
  public function createSetting(array $data): int
  {
    if (!$this->_store->isKeyUnique($data['key'])) {
      throw new \InvalidArgumentException("Key '{$data['key']}' đã tồn tại.");
    }
    return $this->_store->createSetting($data);
  }
  public function updateSetting(int $id, array $data): bool
  {
    $setting = $this->_store->getById($id);
    if ($setting === null || $setting->is_locked) {
      return false;
    }
    if ($data['key'] !== $setting->key && !$this->_store->isKeyUnique($data['key'], $id)) {
      throw new \InvalidArgumentException("Key '{$data['key']}' đã tồn tại.");
    }
    $error = $this->validateByType($setting, $data['value'] ?? null);
    if ($error !== null) {
      throw new \InvalidArgumentException($error);
    }
    return $this->_store->updateSetting($id, $data);
  }
  public function deleteSetting(int $id): bool
  {
    $setting = $this->_store->getById($id);
    if ($setting === null || $setting->is_locked) {
      return false;
    }
    return $this->_store->deleteSetting($id);
  }
  public function isKeyUnique(string $key, ?int $excludeId = null): bool
  {
    return $this->_store->isKeyUnique($key, $excludeId);
  }
  public function getAllGroups(?int $pageTo = null, ?int $limit = null): array
  {
    return $this->_store->getAllGroups($pageTo, $limit);
  }
  public function getTotalGroupsCount(): int
  {
    return $this->_store->getTotalGroupsCount();
  }
  public function validateByType(WebSetting $setting, mixed $value): ?string
  {
    if ($value === null || $value === '') {
      return null;
    }
    $invalid = match ($setting->type) {
      'email' => filter_var($value, FILTER_VALIDATE_EMAIL) === false,
      'url' => filter_var($value, FILTER_VALIDATE_URL) === false,
      'int' => filter_var($value, FILTER_VALIDATE_INT) === false,
      'float' => filter_var($value, FILTER_VALIDATE_FLOAT) === false,
      'bool' => !in_array($value, ['0', '1'], strict: true),
      'json' => function_exists('json_validate') ? !json_validate($value) : json_decode($value) === null && json_last_error() !== JSON_ERROR_NONE,
      'datetime' => strtotime($value) === false,
      default => false,
    };
    if (!$invalid) {
      return null;
    }
    return match ($setting->type) {
      'email' => 'Email không hợp lệ.',
      'url' => 'Đường dẫn URL không hợp lệ.',
      'int' => 'Giá trị phải là số nguyên.',
      'float' => 'Giá trị phải là số thực.',
      'bool' => 'Giá trị phải là 0 hoặc 1.',
      'json' => 'Nội dung không phải JSON hợp lệ.',
      'datetime' => 'Ngày giờ không hợp lệ.',
      default => 'Giá trị không hợp lệ.',
    };
  }
  private function hydrate(WebSetting $setting): WebSetting
  {
    $setting->cast_value = $this->castValue($setting);
    return $setting;
  }
  private function castValue(WebSetting $setting): mixed
  {
    $raw = $setting->value ?? $setting->default_value;
    if ($raw === null) {
      return null;
    }
    return match ($setting->type) {
      'int' => (int) $raw,
      'float' => (float) $raw,
      'bool' => (bool) $raw,
      'json' => json_decode($raw, true),
      default => (string) $raw,
    };
  }
}