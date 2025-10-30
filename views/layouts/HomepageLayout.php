<?php

namespace App\Views\Layouts;

use App\Core\ViewComponent;
use App\Views\Components\Header;
use App\Views\Components\Footer;

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
      <title>Khoa Công nghệ Thông tin - Trường CĐKT Cao Thắng</title>
    </head>

    <body>
      {$Header->render()}
      {$this->content}
      {$Footer->render()}
    </body>

    </html>
    HTML;
  }
}
