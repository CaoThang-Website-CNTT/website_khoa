<?php

namespace App\Cms;

final class AboutPageDefaults
{
  private const IMAGE = 'public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp';

  public static function hero(): array
  {
    return [
      'variant' => 'default',
      'image' => self::IMAGE,
      'badge' => 'Về chúng tôi',
      'title' => 'Câu chuyện của Cao Thắng',
      'subtitle' => 'Từ những ngày đầu tiên đến hôm nay, Cao Thắng không ngừng phát triển để mang đến giáo dục công nghệ chất lượng cao cho sinh viên Việt Nam.',
    ];
  }

  public static function history(): array
  {
    $facultyHistory = [
        'image' => ['src' => self::IMAGE, 'alt' => 'Hoạt động của Khoa Công nghệ thông tin', 'caption' => 'Khoa Công nghệ thông tin Cao Thắng'],
        'year' => '1998',
        'badge' => '<i class="fa-solid fa-graduation-cap"></i> <span class="text-sm">Khoa Công Nghệ Thông Tin</span>',
        'title' => 'Thành lập và phát triển từ năm 1998',
        'timeline' => [
          ['year' => '1998', 'description' => 'Khoa Điện tử - Tin học được thành lập vào tháng 8, ban đầu đào tạo hai ngành Điện tử và Tin học.'],
          ['year' => '2014', 'description' => 'Olympic Tin học Cao Thắng được tổ chức lần đầu, mở đầu sân chơi học thuật thường niên cho sinh viên CNTT.'],
          ['year' => '2018', 'description' => 'Đội tuyển Tin học Cao Thắng giành giải nhất đồng đội khối Cao đẳng tại Olympic Tin học Sinh viên Việt Nam, cùng nhiều giải cá nhân.'],
          ['year' => '2020', 'description' => 'Khoa Điện tử - Tin học được đổi tên thành Khoa Công nghệ thông tin.'],
        ],
      ];
    $schoolHistory = [
        'image' => ['src' => self::IMAGE, 'alt' => 'Trường Cao đẳng Kỹ thuật Cao Thắng', 'caption' => 'Truyền thống đào tạo kỹ thuật Cao Thắng'],
        'year' => '1906',
        'badge' => '<i class="fa-solid fa-building-columns"></i> <span class="text-sm">Trường Cao Đẳng Kỹ Thuật Cao Thắng</span>',
        'title' => 'Hơn một thế kỷ đào tạo kỹ thuật',
        'timeline' => [
          ['year' => '1906', 'description' => 'Trường Cơ khí Á Châu, tiền thân của Trường Cao đẳng Kỹ thuật Cao Thắng, được thành lập.'],
          ['year' => '1915', 'description' => 'Chủ tịch Tôn Đức Thắng theo học tại trường.'],
          ['year' => '2004', 'description' => 'Trường chính thức mang tên Trường Cao đẳng Kỹ thuật Cao Thắng.'],
        ],
      ];

    return ['variant' => 'default', 'sections' => [$schoolHistory, $facultyHistory]];
  }

  public static function bentoGrid(): array
  {
    return ['variant' => 'default', 'items' => [
      self::bento('<i class="fa-solid fa-handshake"></i> <span>Đào tạo gắn doanh nghiệp</span>', 'Thực tiễn', 'Kết nối doanh nghiệp', 'Bộ môn Tin học gắn đào tạo với nhu cầu thực tế và tăng cường kết nối doanh nghiệp.', self::IMAGE, 'Đào tạo Công nghệ thông tin gắn kết doanh nghiệp'),
      self::bento('<i class="fa-solid fa-computer"></i>', '10+', 'Phòng máy & thực hành', 'Hệ thống phòng máy và phòng thực hành phục vụ đào tạo Công nghệ thông tin.'),
      self::bento('<i class="fa-solid fa-graduation-cap"></i>', '4', 'Chương trình đào tạo CNTT', 'Bộ môn Tin học tổ chức bốn chương trình đào tạo thuộc lĩnh vực Công nghệ thông tin.'),
      self::bento('<i class="fa-solid fa-users"></i>', '1.700+', 'Người học liên quan CNTT', 'Hơn 1.700 học sinh, sinh viên theo học các chương trình liên quan đến Công nghệ thông tin.'),
      self::bento('<i class="fa-solid fa-trophy"></i> <span>Olympic Tin học</span>', 'Hạng nhất', 'Đồng đội khối Cao đẳng', 'Đội tuyển Tin học Cao Thắng giành giải nhất đồng đội khối Cao đẳng cùng nhiều giải cá nhân.', self::IMAGE, 'Thành tích Olympic Tin học của đội tuyển Cao Thắng'),
    ]];
  }

  private static function bento(string $badge, string $content, string $subContent, string $footer, string $src = '', string $alt = ''): array
  {
    return compact('badge', 'content', 'subContent', 'footer') + ['image' => compact('src', 'alt')];
  }
}
