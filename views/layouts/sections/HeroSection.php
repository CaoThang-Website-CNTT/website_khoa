<?php

namespace App\Views\Layouts\Sections;

use App\Core\ViewComponent;
use App\Views\Components\{
  Carousel,
};

class HeroSection extends ViewComponent
{
  public function render(): string
  {
    $Carousel = new Carousel([
      [
        'title'       => 'Môi trường học tập',
        'subtitle'    => 'Chuyên nghiệp & Sáng tạo',
        'description' => 'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác, với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận tâm.',
        'button'      => 'Tìm hiểu thêm',
        'image'       => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?...',
        'alt'         => 'Lecture hall with students',
      ],
      [
        'title'       => 'Công nghệ tiên tiến',
        'subtitle'    => 'Hỗ trợ học tập 24/7',
        'description' => 'Hệ thống học trực tuyến hiện đại, tài liệu số hóa đầy đủ, và phòng lab công nghệ cao giúp bạn học mọi lúc, mọi nơi.',
        'button'      => 'Khám phá ngay',
        'image'       => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?...',
        'alt'         => 'Modern computer lab',
      ],
    ]);

    return <<<HTML
    <div class="relative" id="hero-section">
        <div class="container">
          {$Carousel->render()}
        </div>
        <!-- Wave -->
        <div class="wave-container">
          <svg class="wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 60" preserveAspectRatio="none" fill="none">
            <path d="M0 60L48 48.3333C96 38.6667 192 19.3333 288 9.66667C384 0 480 0 576 4.83333C672 9.66667 768 19.3333 864 24.1667C960 29 1056 29 1152 24.1667C1248 19.3333 1344 9.66667 1392 4.83333L1440 0V60H1392C1344 60 1248 60 1152 60C1056 60 960 60 864 60C768 60 672 60 576 60C480 60 384 60 288 60C192 60 96 60 48 60H0Z" fill="white"/>
          </svg>
        </div>
    </div>
    HTML;
  }
}
