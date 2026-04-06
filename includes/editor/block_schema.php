<?php

namespace App\Editor;

/**
 * BlockSchema
 * 
 * Single source of truth cho cấu trúc của từng block type.
 * Mọi block đến từ client đều phải qua validate() trước khi persist.
 * 
 * Khi thêm block type mới: chỉ cần thêm entry vào SCHEMAS.
 * PHP sẽ tự reject bất kỳ field nào không được khai báo (whitelist approach).
 */
class BlockSchema
{
  /**
   * Định nghĩa schema cho từng block type theo version.
   * 
   * Mỗi field có:
   *   - required (bool)
   *   - type: 'string' | 'int' | 'array'
   *   - options: whitelist các giá trị hợp lệ (optional)
   *   - max: độ dài tối đa cho string (optional)
   */
  private const SCHEMAS = [
    'heading' => [
      1 => [
        'text' => ['required' => true, 'type' => 'string', 'max' => 500],
        'level' => ['required' => true, 'type' => 'int', 'options' => [2, 3, 4]],
      ],
    ],

    // Các block type sẽ thêm sau:
    // 'paragraph' => [ 1 => [...] ],
    // 'image'     => [ 1 => [...] ],
    // 'quote'     => [ 1 => [...] ],
    // 'list'      => [ 1 => [...] ],
  ];

  /**
   * Validate toàn bộ mảng blocks từ client.
   * 
   * @param  array $blocks   Mảng block đã json_decode từ hidden input
   * @return array           ['valid' => bool, 'errors' => string[]]
   */
  public static function validateAll(array $blocks): array
  {
    $errors = [];

    foreach ($blocks as $i => $block) {
      $label = "Block[{$i}]";

      // --- Kiểm tra các field bắt buộc ở level block wrapper ---
      if (empty($block['id'])) {
        $errors[] = "{$label}: thiếu 'id'.";
        continue;
      }
      if (empty($block['type'])) {
        $errors[] = "{$label}: thiếu 'type'.";
        continue;
      }
      if (!isset($block['version'])) {
        $errors[] = "{$label}: thiếu 'version'.";
        continue;
      }
      if (!is_array($block['data'] ?? null)) {
        $errors[] = "{$label}: 'data' phải là object.";
        continue;
      }

      // --- Kiểm tra type có được hỗ trợ không ---
      $type = $block['type'];
      $version = (int) $block['version'];

      if (!isset(self::SCHEMAS[$type])) {
        $errors[] = "{$label}: block type '{$type}' không được hỗ trợ.";
        continue;
      }
      if (!isset(self::SCHEMAS[$type][$version])) {
        $errors[] = "{$label}: type '{$type}' không có schema cho version {$version}.";
        continue;
      }

      // --- Validate từng field trong data theo schema ---
      $schema = self::SCHEMAS[$type][$version];
      $data = $block['data'];
      $fieldErrs = self::validateData($data, $schema, $label);

      $errors = array_merge($errors, $fieldErrs);
    }

    return [
      'valid' => empty($errors),
      'errors' => $errors,
    ];
  }

  /**
   * Validate data object của một block theo schema đã khai báo.
   * Whitelist: chỉ chấp nhận field có trong schema, bỏ qua field thừa.
   */
  private static function validateData(array $data, array $schema, string $label): array
  {
    $errors = [];

    foreach ($schema as $field => $rules) {
      $value = $data[$field] ?? null;
      $isEmpty = $value === null || (is_string($value) && trim($value) === '');

      // required check
      if ($rules['required'] && $isEmpty) {
        $errors[] = "{$label}.data.{$field}: bắt buộc phải có giá trị.";
        continue;
      }

      if ($isEmpty)
        continue; // nullable field, bỏ qua các check còn lại

      // type check
      $ok = match ($rules['type']) {
        'string' => is_string($value),
        'int' => is_int($value) || ctype_digit((string) $value),
        'array' => is_array($value),
        default => true,
      };

      if (!$ok) {
        $errors[] = "{$label}.data.{$field}: phải là kiểu {$rules['type']}.";
        continue;
      }

      // max length cho string
      if ($rules['type'] === 'string' && isset($rules['max'])) {
        if (mb_strlen($value, 'UTF-8') > $rules['max']) {
          $errors[] = "{$label}.data.{$field}: không được vượt quá {$rules['max']} ký tự.";
        }
      }

      // whitelist options
      if (isset($rules['options'])) {
        $cast = $rules['type'] === 'int' ? (int) $value : $value;
        if (!in_array($cast, $rules['options'], true)) {
          $allowed = implode(', ', $rules['options']);
          $errors[] = "{$label}.data.{$field}: giá trị không hợp lệ. Chấp nhận: {$allowed}.";
        }
      }
    }

    return $errors;
  }
}
