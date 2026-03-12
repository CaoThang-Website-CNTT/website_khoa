<?php

namespace App\Utils;

class Validator
{
  private array $errors = [];

  public function validate(array $data, array $rules): bool
  {
    foreach ($rules as $field => $fieldRules) {
      $value = $data[$field] ?? null;
      foreach ($fieldRules as $rule) {
        $this->applyRule($field, $value, $rule);
      }
    }
    return empty($this->errors);
  }

  private function applyRule($field, $value, $rule)
  {
    $params = [];
    if (str_contains($rule, ':')) {
      [$rule, $paramStr] = explode(':', $rule);
      $params = explode(',', $paramStr);
    }

    if ($rule === 'required' && (is_null($value) || trim($value) === '')) {
      $this->addError($field, "Trường này không được để trống.");
    } elseif ($rule === 'email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
      $this->addError($field, "Email không hợp lệ.");
    } elseif ($rule === 'max' && $value) {
      $max = (int)$params[0];
      if (mb_strlen($value, 'UTF-8') > $max) {
        $this->addError($field, "Không được vượt quá $max ký tự.");
      }
    } elseif ($rule === 'mssv' && $value && !preg_match('/^\d{10}$/', $value)) {
      $this->addError($field, "Mã số sinh viên phải đúng 10 chữ số.");
    } elseif ($rule === 'phone' && $value && !preg_match('/^[0-9]{10,15}$/', $value)) {
      $this->addError($field, "Số điện thoại không hợp lệ.");
    } elseif ($rule === 'date' && $value && !strtotime($value)) {
      $this->addError($field, "Ngày tháng không hợp lệ.");
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
