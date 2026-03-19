<?php

namespace App\Core;

class Validator
{
  private array $errors = [];

  public function validate(array $data, array $rules): bool
  {
    foreach ($rules as $field => $fieldRules) {
      $value = $data[$field] ?? null;
      foreach ($fieldRules as $rule) {
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
          $this->addError($field, "Trường này không được để trống.");
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
      case 'mssv':
        if ($value && !preg_match('/^\d{10}$/', $value)) {
          $this->addError($field, "Mã số sinh viên phải đúng 10 chữ số.");
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
    }
  }

  public function addError($field, $message)
  {
    $this->errors[$field][] = $message;
  }

  public function getErrors(): array
  {
    return $this->errors;
  }
}
