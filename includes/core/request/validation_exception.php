<?php

namespace App\Core;

/**
 * Exception chuyên biệt cho lỗi validate dữ liệu form.
 * Mang theo danh sách lỗi theo field để controller có thể flash về view.
 */
class ValidationException extends \Exception
{
  private array $errors;

  /**
   * @param array $errors Mảng lỗi theo field. VD: ['email' => ['Email không hợp lệ.']]
   */
  public function __construct(array $errors = [])
  {
    $this->errors = $errors;
    parent::__construct('Dữ liệu không hợp lệ.', 422);
  }

  /**
   * Lấy danh sách lỗi theo field
   * @return array
   */
  public function getErrors(): array
  {
    return $this->errors;
  }
}
