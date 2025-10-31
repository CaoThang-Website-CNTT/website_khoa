<?php

namespace App\Views\Layouts\Sections;

use App\Core\ViewComponent;

class AboutSection extends ViewComponent
{
  private readonly array $contentSections;
  public function __construct(array $content = [
    [
      'title' => 'Đảm bảo chất lượng đào tạo',
      'sub_title' => 'LOREM ISPUM GÌ ĐÓ Ở ĐÂY',
      'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'image' => 'img/about.jpg',
      'alt' => 'Lecture hall with students',
      'card' => [
        'main_content' => 'Top 1',
        'sub_content' => 'Khoa CNTT tại Miền Nam (So với các Cao Đẳng khác)'
      ]
    ],
    [
      'title' => 'Cơ hội Nghề nghiệp',
      'sub_title' => 'LOREM ISPUM GÌ ĐÓ Ở ĐÂY',
      'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'image' => 'img/about.jpg',
      'alt' => 'Lecture hall with students',
      'card' => [
        'main_content' => '98%',
        'sub_content' => 'Tỷ lệ có việc làm'
      ]
    ],
    [
      'title' => 'Nghiên cứu Đột phá',
      'sub_title' => 'LOREM ISPUM GÌ ĐÓ Ở ĐÂY',
      'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'image' => 'img/about.jpg',
      'alt' => 'Lecture hall with students',
      'card' => [
        'main_content' => '50+',
        'sub_content' => 'Doanh nghiệp'
      ]
    ],
  ])
  {
    $this->contentSections = $content;
  }
  public function render(): string
  {
    $content = "";
    foreach ($this->contentSections as $index => $section) {
      $content .= $this->renderAboutItem($index + 1, $section);
    }
    return <<<HTML
    <section class="relative container py-16" id="about-section">
      <div class="container-wrapper flex flex-col">
        {$content}
      </div>
    </section>
    HTML;
  }
  private function renderAboutItem(int $no, array $content): string
  {
    $numberOfText = $no < 10 ? '0' . $no : $no;
    $flexDirection = ($no & 1) == 0 ? 'flex-row-reverse' : 'flex-row';

    return <<<HTML
    <div class="about-item flex gap-12 {$flexDirection}">
      <div class="relative">
        <div class="about-item__image-container overflow-hidden rounded-2xl">
          <div class="image-wrapper">
            <img class="image w-full h-full object-fit" src="{$this->asset($content['image'])}" />
          </div>
        </div>
        <div class="about-item__card absolute z-10 rounded-2xl p-6 flex flex-col gap-1">
          <div class="about-item__card-main-content text-5xl">{$content['card']['main_content']}</div>
          <div class="about-item__card-sub-content text-sm">{$content['card']['sub_content']}</div>
        </div>
      </div>
      <div class="about-item__content-container flex flex-col justify-center gap-4">
        <p class="number-of-text text-7xl">$numberOfText</p>
        <p class="about-item__sub-title text-xs uppercase font-medium">{$content['sub_title']}</p>
        <p class="about-item__title text-4xl">{$content['title']}</p>
        <p class="about-item__content">
          {$content['content']}
        </p>
      </div>
    </div>
    HTML;
  }
}
