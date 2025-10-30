<?php

namespace App\Views\Layouts;

use App\Core\ViewComponent;
use App\Views\Components\{
  Header,
  Carousel,
  WhyChooseUs,
  Footer
};

class HomepageLayout extends ViewComponent
{
  private string $content;
  public function __construct(string $content = 'Khoa CNTT - Cao Thắng')
  {
    $this->content = $content;
  }
  public function render(): string
  {
    $Header = new Header();
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
    $Footer = new Footer();
    $WhyChooseUs = new WhyChooseUs();

    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="icon" type="image/png" sizes="32x32" href="{$this->asset('favicon-32x32.png')}">
      <link rel="preload" as="style" href="{$this->asset('css/fonts.css')}">
      <link rel="stylesheet" href="{$this->asset('css/fonts.css')}">
      <link rel="preload" as="style" href="{$this->asset('css/base.css')}">
      <link rel="stylesheet" href="{$this->asset('css/base.css')}">
      <link rel="preload" as="style" href="{$this->asset('css/main.css')}">
      <link rel="stylesheet" href="{$this->asset('css/main.css')}">
      <link rel="stylesheet" href="{$this->asset('css/carousel.css')}">
      <title>Khoa Công nghệ Thông tin - Trường CĐKT Cao Thắng</title>
    </head>

    <body>
      {$Header->render()}
      <div id="hero-section">
        <div class="container">
          {$Carousel->render()}
        </div>
      </div>
      {$this->content}
      {$WhyChooseUs->render()}
      {$Footer->render()}
    </body>

    </html>
    HTML;
  }
}
