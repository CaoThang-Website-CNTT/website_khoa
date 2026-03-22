<?php

namespace App\Models;

/**
 * Đại diện cho một dòng trong bảng web_settings.
 *
 * $cast_value được service populate sau khi đọc từ DB —
 * giá trị đã được ép kiểu theo $type, sẵn sàng dùng trong view/controller.
 */
class WebSetting
{
  public int $id;
  public string $key;
  public string $group;

  /**
   * Kiểu dữ liệu từ ENUM trong DB.
   * Có thể là: string | text | int | float | bool | json
   *           | color | image | file | email | url | datetime
   */
  public string $type;

  public ?string $value;
  public ?string $default_value;
  public string $label;
  public ?string $description;


  public bool $autoload;
  public bool $is_locked;
  public int $sort_order;
  public ?string $created_at;
  public ?string $updated_at;

  /** ID của admin thực hiện thay đổi cuối. NULL nếu chưa từng cập nhật qua UI. */
  public ?int $updated_by;

  /**
   * Giá trị đã cast — do WebSettingsService populate.
   * Kiểu thực tế phụ thuộc vào $type: int, float, bool, array, string, hoặc null.
   */
  public mixed $cast_value = null;

  public static function fromArray(array $row): self
  {
    $setting = new self();

    $setting->id = (int) $row['id'];
    $setting->key = $row['key'];
    $setting->group = $row['group'];
    $setting->type = $row['type'];
    $setting->value = $row['value'] ?? null;
    $setting->default_value = $row['default_value'] ?? null;
    $setting->label = $row['label'];
    $setting->description = $row['description'] ?? null;
    $setting->autoload = (bool) $row['autoload'];
    $setting->is_locked = (bool) $row['is_locked'];
    $setting->sort_order = (int) $row['sort_order'];
    $setting->created_at = $row['created_at'] ?? null;
    $setting->updated_at = $row['updated_at'] ?? null;
    $setting->updated_by = isset($row['updated_by']) ? (int) $row['updated_by'] : null;

    return $setting;
  }
}