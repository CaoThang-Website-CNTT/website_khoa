<?php
namespace App\Stores;

require_once BASE_PATH . '/models/web_setting.php';
require_once BASE_PATH . '/db/database.php';

use App\Core\Store;
use App\Models\WebSetting;
use PDO;

interface IWebSettingStore
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
  public function getTotalGroupsCount(): int;
  public function isKeyUnique(string $key, ?int $excludeId = null): bool;
  public function getAllGroups(?int $pageTo = null, ?int $limit = null): array;
}
class WebSettingsStore extends Store implements IWebSettingStore
{
  /** @return WebSetting[] */
  public function getAllSettings(): array
  {
    $stmt = $this->db->prepare("SELECT * FROM `web_settings` ORDER BY `group` ASC, `sort_order` ASC, `id` ASC");
    $stmt->execute();
    return array_map(fn($row) => $this->hydrate($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  /** @return WebSetting[] */
  public function getByGroup(string $group): array
  {
    $stmt = $this->db->prepare("SELECT * FROM `web_settings` WHERE `group` = :group ORDER BY `sort_order` ASC, `id` ASC");
    $stmt->execute([':group' => $group]);
    return array_map(fn($row) => $this->hydrate($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  /** @return WebSetting[] */
  public function getAutoloaded(): array
  {
    $stmt = $this->db->prepare("SELECT * FROM `web_settings` WHERE `autoload` = 1 ORDER BY `group` ASC, `sort_order` ASC");
    $stmt->execute();
    return array_map(fn($row) => $this->hydrate($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  public function getById(int $id): ?WebSetting
  {
    $stmt = $this->db->prepare("SELECT * FROM `web_settings` WHERE `id` = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->hydrate($row) : null;
  }
  public function getByKey(string $key): ?WebSetting
  {
    $stmt = $this->db->prepare("SELECT * FROM `web_settings` WHERE `key` = :key");
    $stmt->execute([':key' => $key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $this->hydrate($row) : null;
  }
  public function getValue(string $key, mixed $fallback = null): mixed
  {
    $setting = $this->getByKey($key);
    if ($setting === null)
      return $fallback;
    return $setting->cast_value;
  }
  public function createSetting(array $data): int
  {
    $stmt = $this->db->prepare("INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `default_value`, `label`, `description`, `autoload`, `is_locked`, `sort_order`) VALUES (:key, :group, :type, :value, :default_value, :label, :description, :autoload, :is_locked, :sort_order)");
    $stmt->execute([
      ':key' => $data['key'],
      ':group' => $data['group'] ?? 'general',
      ':type' => $data['type'] ?? 'string',
      ':value' => $data['value'] ?? null,
      ':default_value' => $data['default_value'] ?? null,
      ':label' => $data['label'],
      ':description' => $data['description'] ?? null,
      ':autoload' => $data['autoload'] ?? 1,
      ':is_locked' => $data['is_locked'] ?? 0,
      ':sort_order' => $data['sort_order'] ?? 0,
    ]);
    return (int) $this->db->lastInsertId();
  }
  public function updateSetting(int $id, array $data): bool
  {
    $setting = $this->getById($id);
    if ($setting === null || $setting->is_locked)
      return false;
    $stmt = $this->db->prepare("UPDATE `web_settings` SET `key` = :key, `group` = :group, `type` = :type, `value` = :value, `default_value` = :default_value, `label` = :label, `description` = :description, `autoload` = :autoload, `sort_order` = :sort_order, `updated_by` = :updated_by, `updated_at` = NOW() WHERE `id` = :id");
    return $stmt->execute([
      ':key' => $data['key'],
      ':group' => $data['group'],
      ':type' => $data['type'],
      ':value' => $data['value'] ?? null,
      ':default_value' => $data['default_value'] ?? null,
      ':label' => $data['label'],
      ':description' => $data['description'] ?? null,
      ':autoload' => $data['autoload'] ?? 1,
      ':sort_order' => $data['sort_order'] ?? 0,
      ':updated_by' => $data['updated_by'] ?? null,
      ':id' => $id,
    ]);
  }
  public function deleteSetting(int $id): bool
  {
    $setting = $this->getById($id);
    if ($setting === null || $setting->is_locked)
      return false;
    $stmt = $this->db->prepare("DELETE FROM `web_settings` WHERE `id` = :id");
    return $stmt->execute([':id' => $id]);
  }
  public function getTotalGroupsCount(): int
  {
    $stmt = $this->db->query("SELECT COUNT(DISTINCT `group`) FROM `web_settings`");
    return (int) $stmt->fetchColumn();
  }
  public function isKeyUnique(string $key, ?int $excludeId = null): bool
  {
    $sql = "SELECT COUNT(*) FROM `web_settings` WHERE `key` = :key";
    $params = [':key' => $key];
    if ($excludeId) {
      $sql .= " AND `id` != :exclude_id";
      $params[':exclude_id'] = $excludeId;
    }
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
  }
  public function getAllGroups(?int $pageTo = null, ?int $limit = null): array
  {
    $sql = "SELECT `group` as name, COUNT(id) as total FROM `web_settings` GROUP BY `group` ORDER BY `group` ASC";
    if ($pageTo !== null && $limit !== null) {
      $offset = (max(1, $pageTo) - 1) * $limit;
      $sql .= " LIMIT :limit OFFSET :offset";
    }
    $stmt = $this->db->prepare($sql);
    if ($pageTo !== null && $limit !== null) {
      $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  private function hydrate(array $row): WebSetting
  {
    $setting = WebSetting::fromArray($row);
    $setting->cast_value = $this->castValue($setting);
    return $setting;
  }
  private function castValue(WebSetting $setting): mixed
  {
    $raw = $setting->value ?? $setting->default_value;
    if ($raw === null)
      return null;
    return match ($setting->type) {
      'int' => (int) $raw,
      'float' => (float) $raw,
      'bool' => (bool) $raw,
      'json' => json_decode($raw, true),
      default => (string) $raw,
    };
  }
  public function validateByType(WebSetting $setting, mixed $value): ?string
  {
    if ($value === null || $value === '')
      return null;
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
    if (!$invalid)
      return null;
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
}