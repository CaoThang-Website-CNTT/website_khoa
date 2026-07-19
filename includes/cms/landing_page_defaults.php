<?php

namespace App\Cms;

final class LandingPageDefaults
{
  private const IMAGE = 'public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp';

  public static function about(): array
  {
    return ['variant' => 'default', 'items' => [
      self::aboutItem('01', '1998', 'Khởi đầu Bộ môn Tin học', 'Hình thành & phát triển', 'Hơn hai thập kỷ đào tạo công nghệ thông tin', 'Từ Bộ môn Tin học thuộc Khoa Điện tử - Tin học, Khoa Công nghệ thông tin được tổ chức lại thành khoa chuyên ngành từ năm 2020, tập trung sâu vào đào tạo và ứng dụng CNTT.', 'Hoạt động của Khoa Công nghệ thông tin Cao Thắng'),
      self::aboutItem('02', '12', 'Phòng thực hành CNTT', 'Học đi đôi với hành', 'Không gian thực hành phục vụ kỹ năng nghề nghiệp', 'Hệ thống phòng thực hành hỗ trợ sinh viên rèn luyện lập trình, phần cứng, mạng máy tính và triển khai các sản phẩm công nghệ.', 'Sinh viên thực hành Công nghệ thông tin'),
      self::aboutItem('03', 'Hạng nhất', 'Đồng đội khối Cao đẳng', 'Bản lĩnh sinh viên', 'Khẳng định năng lực tại Olympic Tin học', 'Đội tuyển Tin học Cao Thắng từng giành giải nhất đồng đội khối Cao đẳng cùng nhiều giải cá nhân tại Olympic Tin học Sinh viên Việt Nam.', 'Đội tuyển Olympic Tin học Cao Thắng'),
    ]];
  }

  public static function whyChooseUs(): array
  {
    return [
      'variant' => 'default',
      'badge' => 'Tại sao chọn Khoa CNTT Cao Thắng',
      'title' => 'Nền tảng nghề nghiệp được xây dựng từ thực hành',
      'subtitle' => 'Chương trình chuyên môn, cơ sở thực hành và hoạt động kết nối doanh nghiệp cùng hướng đến khả năng làm việc thực tế của sinh viên.',
      'feature' => [
        'image' => self::IMAGE,
        'alt' => 'Sinh viên Khoa CNTT Cao Thắng học tập và thực hành',
        'badge' => 'Đào tạo gắn thực tiễn',
        'title' => 'Học qua bài tập, dự án và trải nghiệm nghề nghiệp',
        'description' => 'Sinh viên phát triển năng lực từ kiến thức nền tảng đến thực hành chuyên môn, đồ án, thực tập và các hoạt động học thuật của Khoa.',
        'cta_label' => 'Tìm hiểu chương trình',
        'cta_url' => '/dao-tao',
      ],
      'stats' => [
        ['number' => '12', 'title' => 'Phòng thực hành CNTT', 'description' => 'Phục vụ học tập và rèn luyện kỹ năng chuyên môn.'],
        ['number' => '30', 'title' => 'Giảng viên', 'description' => 'Đội ngũ phụ trách đào tạo, nghiên cứu và đồng hành cùng sinh viên.'],
      ],
      'perks' => [
        ['icon' => 'fa-solid fa-graduation-cap', 'title' => 'Ba chương trình đào tạo', 'description' => 'Công nghệ thông tin, Quản trị mạng máy tính và Sửa chữa - lắp ráp máy tính.'],
        ['icon' => 'fa-solid fa-code', 'title' => 'Chuyên môn rõ ràng', 'description' => 'Tổ chức chuyên môn về Công nghệ phần mềm và Phần cứng - Mạng máy tính.'],
        ['icon' => 'fa-solid fa-handshake', 'title' => 'Kết nối doanh nghiệp', 'description' => 'Gắn đào tạo với tham quan, thực tập, tuyển dụng và nhu cầu nhân lực thực tế.'],
        ['icon' => 'fa-solid fa-trophy', 'title' => 'Học thuật và thi đấu', 'description' => 'Olympic Tin học và hoạt động câu lạc bộ giúp sinh viên phát triển tư duy giải quyết vấn đề.'],
      ],
      'highlights' => [
        ['image' => self::IMAGE, 'alt' => 'Sinh viên thực hiện đồ án Công nghệ thông tin', 'title' => 'Đồ án và thực tập', 'description' => 'Vận dụng kiến thức vào sản phẩm, nhiệm vụ và môi trường làm việc thực tế.'],
        ['image' => self::IMAGE, 'alt' => 'Hoạt động kết nối doanh nghiệp của Khoa CNTT', 'title' => 'Đồng hành cùng doanh nghiệp', 'description' => 'Mở rộng trải nghiệm nghề nghiệp thông qua hợp tác đào tạo và tuyển dụng.'],
      ],
    ];
  }

  public static function stats(): array
  {
    return [
      'variant' => 'default',
      'title' => 'Những con số về Khoa CNTT Cao Thắng',
      'subtitle' => 'Nền tảng đào tạo được xây dựng từ chuyên môn, thực hành và hoạt động học thuật.',
      'stats' => [
        ['icon' => 'fa-solid fa-graduation-cap', 'number' => '3', 'label' => 'Chương trình đào tạo', 'description' => 'Ba lộ trình nghề nghiệp thuộc lĩnh vực CNTT.'],
        ['icon' => 'fa-solid fa-code-branch', 'number' => '2', 'label' => 'Bộ môn chuyên môn', 'description' => 'Công nghệ phần mềm và Phần cứng - Mạng máy tính.'],
        ['icon' => 'fa-solid fa-trophy', 'number' => '9', 'label' => 'Kỳ Olympic Tin học', 'description' => 'Olympic Tin học Cao Thắng đã bước sang lần tổ chức thứ 9.'],
        ['icon' => 'fa-solid fa-building-columns', 'number' => '2020', 'label' => 'Khoa CNTT', 'description' => 'Được tổ chức lại thành khoa chuyên ngành Công nghệ thông tin.'],
      ],
      'benefits' => [
        ['icon' => 'fa-solid fa-laptop-code', 'title' => 'Đào tạo hướng đến năng lực thực hành', 'items' => ['Rèn kỹ năng qua bài tập và giờ thực hành chuyên môn', 'Phát triển sản phẩm qua đồ án môn học và đồ án tốt nghiệp', 'Thực tập tốt nghiệp gắn với môi trường nghề nghiệp', 'Bổ sung kỹ năng giao tiếp, làm việc nhóm và trình bày kỹ thuật']],
        ['icon' => 'fa-solid fa-arrow-trend-up', 'title' => 'Phát triển nghề nghiệp cùng doanh nghiệp', 'items' => ['Tham quan và tìm hiểu môi trường làm việc', 'Tiếp cận cơ hội thực tập và tuyển dụng', 'Kết nối nhu cầu doanh nghiệp với hoạt động đào tạo', 'Rèn tư duy và bản lĩnh qua sân chơi học thuật']],
      ],
      'cta' => [
        'title' => 'Sẵn sàng bắt đầu hành trình công nghệ?',
        'description' => 'Khám phá chương trình đào tạo và chọn lộ trình phù hợp với năng lực, sở thích và định hướng nghề nghiệp của bạn.',
        'buttons' => [
          ['label' => 'Xem chương trình đào tạo', 'url' => '/dao-tao', 'variant' => 'outline-alt'],
          ['label' => 'Khám phá cơ hội nghề nghiệp', 'url' => '/danh-muc/tuyen-dung', 'variant' => 'outline'],
        ],
      ],
    ];
  }

  private static function aboutItem(string $number, string $value, string $label, string $eyebrow, string $title, string $description, string $alt): array
  {
    return compact('number', 'eyebrow', 'title', 'description') + [
      'image' => ['src' => self::IMAGE, 'alt' => $alt],
      'card' => compact('value', 'label'),
    ];
  }
}
