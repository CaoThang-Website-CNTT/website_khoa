<?php

namespace App\Models;

abstract class Model
{
  /**
   * Tự động mapping mảng dữ liệu sang Object sử dụng Reflection API.
   * Hỗ trợ tự động ép kiểu dữ liệu an toàn dựa trên property type.
   *
   * @param array $data
   * @return static
   */
  public static function fromArray(array $data): static
  {
    $reflection = new \ReflectionClass(static::class);
    $instance = $reflection->newInstanceWithoutConstructor();

    foreach ($data as $key => $value) {
      if (property_exists($instance, $key)) {
        if ($reflection->hasProperty($key)) {
          $prop = $reflection->getProperty($key);
          $type = $prop->getType();
          if ($type instanceof \ReflectionNamedType) {
            $typeName = $type->getName();
            if ($value !== null) {
              if ($typeName === 'int') {
                $value = (int) $value;
              } elseif ($typeName === 'bool') {
                $value = (bool) $value;
              } elseif ($typeName === 'float') {
                $value = (float) $value;
              } elseif ($typeName === 'array' && is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                  $value = $decoded;
                }
              }
            }
          }
        }
        $instance->{$key} = $value;
      }
    }

    return $instance;
  }

  /**
   * Chuyển đổi Model thành mảng (hữu ích khi trả về JSON hoặc lưu DB).
   * Tự động xử lý các nested relations hoặc array collections của các model con.
   *
   * @return array
   */
  public function toArray(): array
  {
    $properties = get_object_vars($this);
    $result = [];

    foreach ($properties as $key => $value) {
      if ($value instanceof self) {
        $result[$key] = $value->toArray();
      } elseif (is_array($value)) {
        $result[$key] = array_map(function ($item) {
          return $item instanceof self ? $item->toArray() : $item;
        }, $value);
      } else {
        $result[$key] = $value;
      }
    }

    return $result;
  }
}