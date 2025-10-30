<?php

namespace App\Views\Components;

use App\Core\ViewComponent;

class Header extends ViewComponent
{
  private array $navLinks = [
    'Trang Chủ' => [
      'active' => true,
      'dropdown' => [],
    ],
    'Giới Thiệu' => [
      'active' => false,
      'dropdown' => [
        'Giới thiệu chung' => '/gioi-thieu/chung',
        'Lịch sử' => '/gioi-thieu/lich-su',
      ],
    ],
    'Chương Trình Đào Tạo' => [
      'active' => false,
      'dropdown' => [
        'Cao đẳng' => '/dao-tao/cao-dang',
        'Trung cấp' => '/dao-tao/cao-dang-nghe',
      ],
    ],
    'Nghiên Cứu' => [
      'active' => false,
      'dropdown' => [
        'Đề tài' => '/nghien-cuu/de-tai',
        'Công bố' => '/nghien-cuu/cong-bo',
      ],
    ],
    'Tin Tức' => [
      'active' => false,
      'dropdown' => [
        'Sự kiện' => '/tin-tuc/su-kien',
        'Thông báo' => '/tin-tuc/thong-bao',
      ],
    ],
    'Sinh Viên' => [
      'active' => false,
      'dropdown' => [
        'Học bổng' => '/sinh-vien/hoc-bong',
        'Hoạt động' => '/sinh-vien/hoat-dong',
      ],
    ],
    'Liên Hệ' => [
      'active' => false,
      'dropdown' => [
        'Địa chỉ' => '/lien-he/dia-chi',
        'Gửi phản hồi' => '/lien-he/phan-hoi',
      ],
    ],
  ];
  /** Main Render */
  public function render(): string
  {
    return <<<HTML
    <header>
      <div class="sub-header">
        <div class="container flex font-light">
          <div class="">📧 cntt@caothang.edu.vn</div>        
          <div class="">📞 +84 (08) 3821 2360</div>
        </div>
      </div>
      <div class="main-header">
        <div class="container">
          <div class="flex justify-between items-center p-4">
            <div class="web-title flex">
              <div class="web-logo">
                <img src="{$this->asset('img/faculty_logo.jpg')}" alt="Logo Khoa CNTT cua Truong CDKT Cao Thang">
              </div>
              <div class="flex flex-col justify-center">
                <div class="faculty-name uppercase">KHOA CÔNG NGHỆ THÔNG TIN</div>
                <div class="uni-name uppercase">TRƯỜNG CAO ĐẲNG KỸ THUẬT CAO THẮNG</div>
              </div>
            </div>
            <div class="search-bar flex items-center rounded-2xl">
              {$this->icon('search_icon', ['alt' => 'Search Icon'])}
              <input class="search-bar__input" placeholder="Tìm kiếm..." autocomplete="off" autocorrect="off">
            </div>
          </div>
        </div>
        <nav class="navbar">
          <div class="container flex">
            {$this->renderNav()}
          </div>
        </nav>
      </div>
    </header>
    HTML;
  }
  /** Render Navigation Bar */
  private function renderNav(): string
  {
    $items = '';

    foreach ($this->navLinks as $label => $data) {
      $items .= $this->renderNavItem($label, $data);
    }

    return <<<HTML
      {$items}
    HTML;
  }
  /** Render Single Nav Item */
  private function renderNavItem(string $label, array $data): string
  {
    $active = $data['active'] ?? false;

    $class = "class='navbar__item flex items-center" . ($active ? ' navbar__item--active' : '') . "'";

    $dropdownMenu = !empty($data['dropdown']) ? $this->renderDropdown($data['dropdown']) : '';

    return <<<HTML
    <div {$class}>
      <div class="uppercase">
        {$label}
      </div>
      {$dropdownMenu}
    </div>
    HTML;
  }
  private function renderDropdown(array $dropdownItems): string
  {
    $items = '';

    foreach ($dropdownItems as $label => $url) {
      $items .= <<<HTML
      <a class="dropdown-menu__item flex justify-between" href="{$url}">
        <div>{$label}</div>
        <div>
          {$this->icon('chevron_right', ['alt' => 'Dropdown Item Go-to Icon'])}
        </div>
      </a>
      HTML;
    }

    return <<<HTML
    {$this->icon('chevron_down', ['alt' => 'Dropdown Icon'])}
    <div class="dropdown-menu">
      {$items}
    </div>
    HTML;
  }
}
