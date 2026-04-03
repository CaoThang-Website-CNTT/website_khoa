<?php

namespace App\Models;

abstract class Model
{
  /**
   * Tự động mapping mảng dữ liệu (ví dụ: từ PDO) sang Object.
   * * @param array $data
   * @return static
   */
  public static function fromArray(array $data): static
  {
    // Khởi tạo instance của class con (ví dụ: Student)
    // Lưu ý: Class con phải có giá trị mặc định cho tất cả các tham số trong constructor
    $instance = new static();

    foreach ($data as $key => $value) {
      // Chỉ gán giá trị nếu thuộc tính tồn tại trên model để tránh lỗi
      if (property_exists($instance, $key)) {
        $instance->{$key} = $value;
      }
    }

    return $instance;
  }

  /**
   * Chuyển đổi Model thành mảng (hữu ích khi trả về JSON hoặc lưu DB).
   * Tự động xử lý các nested relations nếu chúng cũng kế thừa từ Model.
   * * @return array
   */
  public function toArray(): array
  {
    // Lấy tất cả các public/protected properties của object hiện tại
    $properties = get_object_vars($this);
    $result = [];

    foreach ($properties as $key => $value) {
      // Nếu property là một model khác (như Account, Classroom), đệ quy toArray
      if ($value instanceof self) {
        $result[$key] = $value->toArray();
      } else {
        $result[$key] = $value;
      }
    }

    return $result;
  }
}