<?php

namespace App\Core;

class RequestValidator
{
  private array $errors = [];

  public function validate(array $data, array $rules): bool
  {
    foreach ($rules as $field => $fieldRules) {
      $value = $this->getValueByPath($data, $field);

      $isEmpty = $value === null || trim((string) $value) === '';
      if ($isEmpty && in_array('nullable', $fieldRules)) {
        continue;
      }

      foreach ($fieldRules as $rule) {
        // Skip 'nullable' rule vì đã xử lý ở trên
        if ($rule === 'nullable') {
          continue;
        }

        $this->applyRule($field, $value, $rule, $data);
      }
    }
    return empty($this->errors);
  }

  private function applyRule($field, $value, $rule, $data)
  {
    $param = null;
    if (str_contains($rule, ':')) {
      [$rule, $param] = explode(':', $rule);
    }

    switch ($rule) {
      case 'required':
        if (is_null($value) || trim($value) === '') {
          $this->addError($field, "Ô này không được để trống.");
          return;
        }
        break;
      case 'password':
        $pattern = '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/';
        if ($value && !preg_match($pattern, $value)) {
          $this->addError($field, "Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.");
        }
        break;
      case 'same':
        $otherField = $param;
        if ($value !== ($data[$otherField] ?? null)) {
          $this->addError($field, "Giá trị xác nhận không khớp.");
        }
        break;
      case 'email':
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
          $this->addError($field, "Email không hợp lệ.");
        }
        break;
      case 'max':
        $max = (int) $param;
        if (mb_strlen($value, 'UTF-8') > $max) {
          $this->addError($field, "Không được vượt quá $max ký tự.");
        }
        break;
      case 'size':
        $size = (int) $param;
        if ($value && mb_strlen((string) $value, 'UTF-8') !== $size) {
          $this->addError($field, "Phải có chính xác $size ký tự.");
        }
        break;
      case 'in':
        $allowed = explode(',', $param);
        if ($value && !in_array((string) $value, $allowed, true)) {
          $this->addError($field, "Giá trị đã chọn không hợp lệ.");
        }
        break;
      case 'phone':
        if ($value && !preg_match('/^[0-9]{10,15}$/', $value)) {
          $this->addError($field, "Số điện thoại không hợp lệ.");
        }
        break;
      case 'date':
        if ($value && !strtotime($value)) {
          $this->addError($field, "Ngày tháng không hợp lệ.");
        }
        break;
      case 'json':
        if ($value) {
          if (!is_string($value)) {
            $this->addError($field, "Trường này phải là một chuỗi JSON.");
          } else {
            json_decode($value);
            if (json_last_error() !== JSON_ERROR_NONE) {
              $this->addError($field, "Trường này phải là một chuỗi JSON hợp lệ.");
            }
          }
        }
        break;
    }
  }

  public function addError($field, $message)
  {
    $this->errors[$field][] = $message;
  }

  public function hasErrors(): bool
  {
    return !empty($this->errors);
  }

  public function getErrors(): array
  {
    return $this->errors;
  }

  /**
   * Xử lý dot annotation
   * @param array $data
   * @param string $path
   */
  private function getValueByPath(array $data, string $path)
  {
    $keys = explode('.', $path);
    foreach ($keys as $key) {
      if (is_array($data) && array_key_exists($key, $data)) {
        $data = $data[$key];
      } else {
        return null;
      }
    }
    return $data;
  }
}
