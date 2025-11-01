<?php

namespace App\Views\Layouts;

use App\Core\ViewComponent;
use App\Views\Components\{
  Header,
  Footer
};
use App\Views\Layouts\Sections\{
  HeroSection,
  AboutSection,
  BaseSection,
  WhyChooseUsSection,
  NewsFeedSection,
};

class HomepageLayout extends ViewComponent
{
  private string $content;
  public function __construct(string $content)
  {
    $this->content = $content;
  }
  public function render(): string
  {
    $Header = new Header();
    /** Không dùng base section cho Hero (do có phần tử đặc biệt) */
    $Hero = new HeroSection();
    $About = new BaseSection([
      'id' => 'about-section',
      'title' => '',
      'sub_title' => '',
      'badge' => '',
      'children' => new AboutSection()
    ]);
    $WhyChooseUs = new BaseSection([
      'id' => 'why-choose-us-section',
      'title' => 'Trải nghiệm Khoa CNTT Cao Thắng',
      'sub_title' => 'Nơi ươm mầm tài năng công nghệ thông tin, kết nối tri thức với thực tiễn',
      'badge' => 'Tại sao chọn chúng tôi',
      'children' => new WhyChooseUsSection()
    ]);
    $NewsFeed = new BaseSection([
      'id' => 'newsfeed-section',
      'title' => 'Tin tức & Sự kiện',
      'sub_title' => 'Cập nhật những tin tức mới nhất về hoạt động của khoa, thành tích sinh viên và các sự kiện sắp tới',
      'children' => new NewsFeedSection()
    ]);
    $Footer = new Footer();

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
      {$Hero->render()}
      {$About->render()}
      {$WhyChooseUs->render()}
      {$NewsFeed->render()}
      {$Footer->render()}
    </body>

    </html>
    HTML;
  }
}
