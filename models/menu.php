<?php

namespace App\Models;

class Menu
{
  public function __construct(
    public int $id,
    public string $type,
    public string $key,
    public string $label,
    public ?string $description,
    public int $sort_order,
    public string $created_at,
    public string $updated_at,
  ) {
  }

  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @return Menu
   */
  public static function fromArray(array $data): self
  {
    return new self(
      id: (int) $data['id'],
      type: $data['type'],
      created_at: $data['created_at'],
      key: $data['key'],
      label: $data['label'],
      description: $data['description'] ?? null,
      sort_order: (int) $data['sort_order'],
      updated_at: $data['updated_at'],
    );
  }

  /**
   * Kiểm tra menu có phải loại tĩnh do dev định nghĩa không.
   * Menu loại này không được phép chỉnh sửa qua admin.
   *
   * @return bool
   */
  public function isConst(): bool
  {
    return $this->type === 'const';
  }

  /**
   * Kiểm tra menu có phải loại do admin tạo không.
   * Menu loại này được phép chỉnh sửa tự do qua admin.
   *
   * @return bool
   */
  public function isCustom(): bool
  {
    return $this->type === 'custom';
  }

  /**
   * Kiểm tra menu có cho phép chỉnh sửa không.
   * Dùng trong controller/view để guard các action update/delete.
   *
   * @return bool
   */
  public function isEditable(): bool
  {
    return $this->type === 'custom';
  }
}