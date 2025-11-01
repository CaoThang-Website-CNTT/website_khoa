<?php

namespace App\Views\Layouts\Sections;

use App\Core\ViewComponent;

class NewsFeedSection extends ViewComponent
{
  private array $featured_news = [
    [
      'title' => 'Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025',
      'sub_title' => 'Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để đạt được danh hiệu cao quý',
      'image' => [
        'src' => 'https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1336',
        'alt' => 'This is an image of a student won a tournament'
      ],
      'published_at' => '15/01/2025',
      'tag' => 'Nổi bật',
      'url' => '#'
    ],
    [
      'title' => 'Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025',
      'sub_title' => 'Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để đạt được danh hiệu cao quý',
      'image' => [
        'src' => 'https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1336',
        'alt' => 'This is an image of a student won a tournament'
      ],
      'published_at' => '15/01/2025',
      'tag' => 'Thành tích',
      'url' => '#'
    ],
    [
      'title' => 'Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025',
      'sub_title' => 'Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để đạt được danh hiệu cao quý',
      'image' => [
        'src' => 'https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1336',
        'alt' => 'This is an image of a student won a tournament'
      ],
      'published_at' => '15/01/2025',
      'tag' => 'Thành tích',
      'url' => '#'
    ],
    [
      'title' => 'Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025',
      'sub_title' => 'Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để đạt được danh hiệu cao quý',
      'image' => [
        'src' => 'https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1336',
        'alt' => 'This is an image of a student won a tournament'
      ],
      'published_at' => '15/01/2025',
      'tag' => 'Thành tích',
      'url' => '#'
    ]
  ];

  private array $other_news = [
    [
      'title' => 'Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025',
      'sub_title' => 'Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để đạt được danh hiệu cao quý',
      'image' => [
        'src' => 'https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1336',
        'alt' => 'This is an image of a student won a tournament'
      ],
      'published_at' => '15/01/2025',
      'tag' => 'Thành tích',
      'url' => '#'
    ],
    [
      'title' => 'Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2024',
      'sub_title' => 'Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để đạt được danh hiệu',
      'image' => [
        'src' => 'https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1336',
        'alt' => 'This is an image of a student won a tournament'
      ],
      'published_at' => '15/01/2024',
      'tag' => 'Thành tích',
      'url' => '#'
    ],
    [
      'title' => 'Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025',
      'sub_title' => 'Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để đạt được danh hiệu cao quý',
      'image' => [
        'src' => 'https://images.unsplash.com/photo-1761602545494-4cd002b4d2b2?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1336',
        'alt' => 'This is an image of a student won a tournament'
      ],
      'published_at' => '15/01/2025',
      'tag' => 'Thành tích',
      'url' => '#'
    ]
  ];
  /**
   * Render tin tức nổi bật (headline).
   *
   * @param array $news Dữ liệu tin tức
   * @return string HTML của headline
   */
  public function render_headline_news(array $news): string
  {
    $image_url = $news['image']['src'] ?? '';
    $image_alt = $news['image']['alt'] ?? '';
    $tag = $news['tag'] ?? '';
    $published_at = $news['published_at'] ?? '';
    $title = $news['title'] ?? '';
    $sub_title = $news['sub_title'] ?? '';
    $url = $news['url'] ?? '#';

    return <<<HTML
      <div class="news__item relative overflow-hidden">
        <div class="image-wrapper">
          <img src="{$image_url}" alt="{$image_alt}" class="absolute w-full h-full object-cover image">
          <div class="news__item--overlay absolute inset-0 flex flex-col justify-end items-start text-white p-16">
            <div class="mb-2">
              <span class="news__item--tag rounded-full text-center font-normal text-sm px-3 mb-2">{$tag}</span>
              <span class="text-base">{$published_at}</span>
            </div>
            <h3 class="news__item--title text-4xl font-semibold mb-2">{$title}</h3>
            <p class="news__item--sub_title text-xl font-normal mb-6">{$sub_title}</p>
            <a href="{$url}" class="text-base text-primary font-normal bg-white px-4 py-2 rounded-full">Đọc thêm
              {$this->icon('top_right_arrow_icon', ['alt' => 'Top right arrow Icon'])}
            </a>
          </div>
        </div>
      </div>
    HTML;
  }
  /**
   * Render danh sách tin tức nổi bật (1 headline + 3 phụ).
   *
   * @param array $featured_news Danh sách tin nổi bật
   * @return string HTML
   */
  public function render_featured_news(array $featured_news): string
  {
    $headline_news = $featured_news[0] ?? [];
    $other_featured = array_slice($featured_news, 1, 3);

    $html = $this->render_headline_news($headline_news);

    $html .= '<div class="flex gap-6 justify-center items-stretch self-stretch">';
    foreach ($other_featured as $news) {
      $html .= $this->render_news_item($news);
    }
    $html .= '</div>';

    return '<div id="featured-news" class="flex flex-col gap-8">' . $html . '</div>';
  }
  /**
   * Render một mục tin tức nhỏ (dùng cho featured & other).
   *
   * @param array $news Dữ liệu tin tức
   * @return string HTML
   */
  public function render_news_item(array $news): string
  {
    $url = $news['url'] ?? '#';
    $image_url = $news['image']['src'] ?? '';
    $image_alt = $news['image']['alt'] ?? '';
    $tag = $news['tag'] ?? '';
    $published_at = $news['published_at'] ?? '';
    $title = $news['title'] ?? '';
    $sub_title = $news['sub_title'] ?? '';

    return <<<HTML
      <a href="{$url}" class="news__item flex-1 overflow-hidden relative rounded-2xl">
        <div class="image-wrapper">
          <img src="{$image_url}" alt="{$image_alt}" class="absolute w-full h-full object-cover image">
          <div class="news__item--overlay absolute inset-0 flex flex-col justify-end items-start text-white p-4">
            <div class="mb-2">
              <span class="news__item--tag rounded-full text-center font-normal text-xs px-3">{$tag}</span>
              <span class="text-sm">{$published_at}</span>
            </div>
            <h3 class="news__item--title text-xl font-semibold mb-2">{$title}</h3>
            <p class="news__item--sub_title text-sm font-normal">{$sub_title}</p>
          </div>    
        </div>
      </a>
    HTML;
  }

  /**
   * Render danh sách tin tức khác.
   *
   * @param array $other_news Danh sách tin tức khác
   * @return string HTML
   */
  public function render_other_news_section(array $other_news): string
  {
    $items = '';
    foreach ($other_news as $news) {
      $items .= $this->render_news_item($news);
    }
    return '<div class="flex gap-6 justify-center items-stretch self-stretch">' . $items . '</div>';
  }

  /** Main Render */
  public function render(): string
  {
    return <<<HTML
      <div class="container-wrapper">
        <!--featured news-->
        {$this->render_featured_news($this->featured_news)}
        <!---->
        <div class="mb-8 flex">
          <h2 class="text-4xl font-medium flex-1">Tin tức khác</h2>
          <a href="#" class="text-base text-primary font-medium px-4 py-2">Xem thêm
            {$this->icon('top_right_arrow_icon', ['alt' => 'Top right arrow Icon'])}
          </a>
        </div>
        <!--other news-->
        {$this->render_other_news_section($this->other_news)}
        <!---->
        </div>
      </div>
    HTML;
  }
}
