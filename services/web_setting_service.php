<?php

namespace App\Services;

require_once BASE_PATH . '/models/web_setting.php';
require_once BASE_PATH . '/db/database.php';

use App\Models\WebSetting;
use Database;
use PDO;

// ============================================================================
// Interface
// ============================================================================
interface IWebSettingRepository
{
  /**
   * Lấy toàn bộ settings, nhóm và sắp xếp theo group + sort_order.
   *
   * @return WebSetting[]
   */
  public function getAllSettings(): array;

  /**
   * Lấy tất cả settings thuộc một nhóm cụ thể.
   *
   * @return WebSetting[]
   */
  public function getByGroup(string $group): array;

  /**
   * Lấy các settings có autoload = 1.
   * Đây là điểm tích hợp cache — load một lần, tái sử dụng mọi request.
   *
   * @return WebSetting[]
   */
  public function getAutoloaded(): array;

  public function getById(int $id): ?WebSetting;
  public function getByKey(string $key): ?WebSetting;

  /**
   * Lấy giá trị đã cast của một setting theo key.
   * Nếu value NULL, tự động fallback về default_value.
   * Nếu key không tồn tại trong DB, trả về $fallback.
   */
  public function getValue(string $key, mixed $fallback = null): mixed;

  public function createSetting(array $data): int;

  /**
   * Cập nhật toàn bộ thông tin một setting theo ID.
   * Trả về false ngay lập tức nếu setting có is_locked = 1.
   */
  public function updateSetting(int $id, array $data): bool;

  /**
   * Xóa cứng một setting theo ID.
   * Trả về false ngay lập tức nếu setting có is_locked = 1.
   */
  public function deleteSetting(int $id): bool;

  public function isKeyUnique(string $key, ?int $excludeId = null): bool;

  /** @return string[] Danh sách group đang tồn tại, dùng cho datalist gợi ý */
  public function getGroups(): array;
}

// ============================================================================
// Service
// ============================================================================
class WebSettingsService implements IWebSettingRepository
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }

  // --------------------------------------------------------------------------
  // Read
  // --------------------------------------------------------------------------

  /**
   * Lấy toàn bộ settings, sắp xếp theo group rồi sort_order.
   *
   * @public
   * @return WebSetting[]
   */
  public function getAllSettings(): array
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `web_settings`
      ORDER BY `group` ASC, `sort_order` ASC, `id` ASC
    ");
    $stmt->execute();

    return array_map(
      fn($row) => $this->hydrate($row),
      $stmt->fetchAll(PDO::FETCH_ASSOC)
    );
  }

  /**
   * Lấy tất cả settings thuộc một group, sắp xếp theo sort_order.
   *
   * @public
   * @param string $group Tên nhóm, VD: 'general', 'homepage', 'seo'
   * @return WebSetting[]
   */
  public function getByGroup(string $group): array
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `web_settings`
      WHERE `group` = :group
      ORDER BY `sort_order` ASC, `id` ASC
    ");
    $stmt->execute([':group' => $group]);

    return array_map(
      fn($row) => $this->hydrate($row),
      $stmt->fetchAll(PDO::FETCH_ASSOC)
    );
  }

  /**
   * Lấy các settings được đánh dấu autoload = 1.
   *
   * Đây là điểm tích hợp cache trong tương lai:
   * - Trước khi query DB, kiểm tra cache (APCu / file cache / Redis).
   * - Nếu cache hit: unserialize và trả về ngay.
   * - Nếu cache miss: query DB, lưu vào cache, rồi trả về.
   * - Invalidate cache mỗi khi updateSetting() hoặc deleteSetting() thành công.
   *
   * @public
   * @return WebSetting[]
   */
  public function getAutoloaded(): array
  {
    // TODO: kiểm tra cache ở đây trước khi query DB
    // VD: $cached = CacheService::get('web_settings.autoloaded');
    //     if ($cached !== null) return $cached;

    $stmt = $this->db->prepare("
      SELECT * FROM `web_settings`
      WHERE `autoload` = 1
      ORDER BY `group` ASC, `sort_order` ASC
    ");
    $stmt->execute();

    $results = array_map(
      fn($row) => $this->hydrate($row),
      $stmt->fetchAll(PDO::FETCH_ASSOC)
    );

    // TODO: lưu vào cache ở đây
    // VD: CacheService::set('web_settings.autoloaded', $results, ttl: 3600);

    return $results;
  }

  /**
   * Tìm một setting theo ID.
   *
   * @public
   * @param int $id
   * @return WebSetting|null Trả về null nếu không tìm thấy
   */
  public function getById(int $id): ?WebSetting
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `web_settings` WHERE `id` = :id
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? $this->hydrate($row) : null;
  }

  /**
   * Tìm một setting theo key duy nhất.
   * Dùng trong code để đọc từng setting cụ thể.
   * Ví dụ: getByKey('homepage.hero_title')
   *
   * @public
   * @param string $key
   * @return WebSetting|null Trả về null nếu không tìm thấy
   */
  public function getByKey(string $key): ?WebSetting
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `web_settings` WHERE `key` = :key
    ");
    $stmt->execute([':key' => $key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? $this->hydrate($row) : null;
  }

  /**
   * Lấy giá trị đã cast của một setting theo key.
   *
   * Thứ tự ưu tiên:
   *   1. `value` nếu không NULL
   *   2. `default_value` nếu value NULL
   *   3. $fallback nếu key không tồn tại trong DB
   *
   * @public
   * @param string $key   Key cần tra cứu
   * @param mixed $fallback Giá trị trả về khi key không tồn tại trong DB
   * @return mixed Giá trị đã cast theo type (int, float, bool, array, string) hoặc null
   */
  public function getValue(string $key, mixed $fallback = null): mixed
  {
    $setting = $this->getByKey($key);

    if ($setting === null)
      return $fallback;

    return $setting->cast_value;
  }

  // --------------------------------------------------------------------------
  // Write
  // --------------------------------------------------------------------------

  /**
   * Tạo mới một setting.
   *
   * @public
   * @param array $data Gồm: key, group, type, value, default_value,
   *                         label, description, autoload, is_locked, sort_order
   * @return int ID của setting vừa tạo
   */
  public function createSetting(array $data): int
  {
    $stmt = $this->db->prepare("
      INSERT INTO `web_settings`
        (`key`, `group`, `type`, `value`, `default_value`,
         `label`, `description`, `autoload`, `is_locked`, `sort_order`)
      VALUES
        (:key, :group, :type, :value, :default_value,
         :label, :description, :autoload, :is_locked, :sort_order)
    ");
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

  /**
   * Cập nhật toàn bộ thông tin một setting theo ID.
   * Từ chối ngay nếu setting có is_locked = 1.
   *
   * Lưu ý: is_locked không thể thay đổi qua method này —
   * đây là flag hệ thống, chỉ chỉnh trực tiếp trong DB hoặc migration.
   *
   * @public
   * @param int   $id   ID của setting cần cập nhật
   * @param array $data Gồm: key, group, type, value, default_value,
   *                         label, description, autoload, sort_order
   * @return bool False nếu không tìm thấy, đang bị khoá, hoặc query thất bại
   */
  public function updateSetting(int $id, array $data): bool
  {
    $setting = $this->getById($id);

    if ($setting === null)
      return false;
    if ($setting->is_locked)
      return false;

    $stmt = $this->db->prepare("
      UPDATE `web_settings` SET
        `key`           = :key,
        `group`         = :group,
        `type`          = :type,
        `value`         = :value,
        `default_value` = :default_value,
        `label`         = :label,
        `description`   = :description,
        `autoload`      = :autoload,
        `sort_order`    = :sort_order,
        `updated_by`    = :updated_by,
        `updated_at`    = NOW()
      WHERE `id` = :id
    ");

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

  /**
   * Xóa cứng một setting theo ID.
   * Từ chối ngay nếu setting có is_locked = 1.
   *
   * @public
   * @param int $id ID của setting cần xóa
   * @return bool False nếu không tìm thấy, đang bị khoá, hoặc query thất bại
   */
  public function deleteSetting(int $id): bool
  {
    $setting = $this->getById($id);

    if ($setting === null)
      return false;
    if ($setting->is_locked)
      return false;

    $stmt = $this->db->prepare("
      DELETE FROM `web_settings` WHERE `id` = :id
    ");

    return $stmt->execute([':id' => $id]);
  }

  /**
   * Kiểm tra key có duy nhất trong bảng web_settings hay không.
   * Có thể loại trừ một ID cụ thể khi dùng cho trường hợp cập nhật.
   *
   * @public
   * @param string   $key       Key cần kiểm tra
   * @param int|null $excludeId ID cần loại trừ (dùng khi update)
   * @return bool True nếu key chưa được dùng
   */
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

  /**
   * Lấy danh sách các group đang tồn tại trong DB, sắp xếp theo alphabet.
   * Dùng để populate datalist gợi ý trong form tạo setting mới —
   * giúp admin chọn group có sẵn thay vì gõ tay, tránh typo tạo group trùng lặp.
   *
   * @public
   * @return string[] VD: ['contact', 'general', 'homepage', 'seo', 'social']
   */
  public function getGroups(): array
  {
    $stmt = $this->db->prepare("
      SELECT DISTINCT `group` FROM `web_settings`
      ORDER BY `group` ASC
    ");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  // --------------------------------------------------------------------------
  // Private helpers
  // --------------------------------------------------------------------------

  /**
   * Tạo WebSetting từ row DB và tự động cast giá trị theo type.
   *
   * @param array $row Row thô từ PDO::FETCH_ASSOC
   * @return WebSetting
   */
  private function hydrate(array $row): WebSetting
  {
    $setting = WebSetting::fromArray($row);
    $setting->cast_value = $this->castValue($setting);

    return $setting;
  }

  /**
   * Ép kiểu giá trị của setting theo trường $type.
   *
   * Thứ tự ưu tiên nguồn dữ liệu:
   *   1. $setting->value nếu không NULL
   *   2. $setting->default_value nếu value NULL
   *   3. null nếu cả hai đều NULL
   *
   * Bảng ánh xạ type → PHP type:
   *   int                              → (int)
   *   float                            → (float)
   *   bool                             → (bool) — DB lưu '0' / '1'
   *   json                             → array  — json_decode associative
   *   string, text, email, url, datetime → (string)
   *
   * @param WebSetting $setting
   * @return mixed
   */
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
      default => (string) $raw, // string, text, email, url, datetime
    };
  }

  /**
   * Validate giá trị của setting dựa hoàn toàn vào $type — không cần rules riêng.
   *
   * Bảng ánh xạ type → validation:
   *   email    → filter_var FILTER_VALIDATE_EMAIL
   *   url      → filter_var FILTER_VALIDATE_URL
   *   int      → filter_var FILTER_VALIDATE_INT
   *   float    → filter_var FILTER_VALIDATE_FLOAT
   *   bool     → chỉ nhận '0' hoặc '1'
   *   json     → json_validate() — PHP 8.3+, fallback json_decode() cho PHP cũ hơn
   *   datetime → strtotime()
   *   string, text → luôn hợp lệ, không validate format
   *
   * Trả về null nếu hợp lệ, chuỗi thông báo lỗi nếu không hợp lệ.
   *
   * @param  WebSetting $setting
   * @param  mixed      $value   Giá trị raw từ POST, chưa cast.
   * @return string|null
   */
  public function validateByType(WebSetting $setting, mixed $value): ?string
  {
    // NULL và chuỗi rỗng đều được chấp nhận — required không được enforce ở đây
    if ($value === null || $value === '')
      return null;

    $invalid = match ($setting->type) {
      'email' => filter_var($value, FILTER_VALIDATE_EMAIL) === false,
      'url' => filter_var($value, FILTER_VALIDATE_URL) === false,
      'int' => filter_var($value, FILTER_VALIDATE_INT) === false,
      'float' => filter_var($value, FILTER_VALIDATE_FLOAT) === false,
      'bool' => !in_array($value, ['0', '1'], strict: true),
      'json' => function_exists('json_validate')
      ? !json_validate($value)
      : json_decode($value) === null && json_last_error() !== JSON_ERROR_NONE,
      'datetime' => strtotime($value) === false,
      default => false, // string, text — không validate format
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