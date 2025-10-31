<?php

namespace App\Views\Components;

use App\Core\ViewComponent;

class Footer extends ViewComponent
{
  private array $quick_links = [
    ['title' => 'Giới thiệu', 'link' => '#'],
    ['title' => 'Chương trình đào tạo', 'link' => '#'],
    ['title' => 'Tuyển sinh', 'link' => '#'],
    ['title' => 'Nghiên cứu khoa học', 'link' => '#'],
    ['title' => 'Sinh viên', 'link' => '#'],
    ['title' => 'Cựu sinh viên', 'link' => '#'],
  ];

  private array $education_programs = [
    ['title' => 'Công nghệ phần mềm', 'link' => '#'],
    ['title' => 'Lập trình di động', 'link' => '#'],
    ['title' => 'Công nghệ phần mềm', 'link' => '#'],
    ['title' => 'Lập trình website', 'link' => '#'],
    ['title' => 'Công nghệ phần mềm', 'link' => '#'],
    ['title' => 'Trí tuệ nhân tạo', 'link' => '#'],
  ];

  private array $contact_infos = [
    [
      'icon' => ['file_name' => 'address_icon', 'alt' => 'Address Icon'],
      'content' => 'Lầu 7 - Dãy F, 65 Huỳnh Thúc Kháng, Phường Sài Gòn, TP.HCM, Việt Nam'
    ],
    [
      'icon' => ['file_name' => 'phone_icon', 'alt' => 'Phone Icon'],
      'content' => '+84 (08) 3821 2360'
    ],
    [
      'icon' => ['file_name' => 'email_icon', 'alt' => 'Email Icon'],
      'content' => 'cntt@caothang.edu.vn'
    ]
  ];

  private array $social_links = [
    ['icon' => ['file_name' => 'facebook_icon', 'alt' => 'Facebook Icon'], 'link' => '#'],
    ['icon' => ['file_name' => 'youtube_icon', 'alt' => 'Youtube Icon'], 'link' => '#'],
    ['icon' => ['file_name' => 'instagram_icon', 'alt' => 'Instagram Icon'], 'link' => '#']
  ];

  /**
   * Render danh sách liên kết nhanh hoặc chương trình đào tạo.
   *
   * @param string $title Tiêu đề cột
   * @param array $items Danh sách các mục liên kết
   * @return string HTML của danh sách
   */
  private function render_link_list(string $title, array $items): string
  {
    $html = '<h3 class="text-white font-normal mb-4">' . htmlspecialchars($title) . '</h3><ul>';
    foreach ($items as $item) {
      $link_title = $item['title'] ?? '';
      $link_url = $item['link'] ?? '#';
      $html .= <<<HTML
        <li class="text-sm font-normal text-footer mb-2">
            <a href="{$link_url}">{$link_title}</a>
        </li>
      HTML;
    }
    $html .= '</ul>';
    return '<div class="footer-content__item">' . $html . '</div>';
  }

  /**
   * Render danh sách thông tin liên hệ.
   *
   * @param string $title Tiêu đề cột
   * @param array $contacts Danh sách thông tin liên hệ
   * @return string HTML của danh sách liên hệ
   */
  private function render_contact_list(string $title, array $contacts): string
  {
    $html = '<h3 class="text-white font-normal mb-4">' . htmlspecialchars($title) . '</h3><ul>';
    foreach ($contacts as $contact) {
      $icon = $contact['icon'] ?? [];
      $content = $contact['content'] ?? '';
      $icon_html = (!empty($icon['file_name']) && !empty($icon['alt']))
        ? $this->icon($icon['file_name'], ['alt' => $icon['alt']])
        : '';
      $html .= <<<HTML
        <li class="mb-3 flex gap-3">
          <span class="text-primary-alt">{$icon_html}</span>
          <p class="text-sm font-normal text-footer">{$content}</p>
        </li>
      HTML;
    }
    $html .= '</ul>';
    return $html;
  }

  /**
   * Render danh sách mạng xã hội.
   *
   * @param string $title Tiêu đề cột
   * @param array $socials Danh sách mạng xã hội
   * @return string HTML của mạng xã hội
   */
  private function render_social_links(string $title, array $socials): string
  {
    $html = '<h3 class="text-white font-normal mb-4">' . htmlspecialchars($title) . '</h3>';
    $html .= '<ul class="flex g-2 items-center">';
    foreach ($socials as $social) {
      $icon = $social['icon'] ?? [];
      $link = $social['link'] ?? '#';
      $icon_html = (!empty($icon['file_name']) && !empty($icon['alt']))
        ? $this->icon($icon['file_name'], ['alt' => $icon['alt']])
        : '';
      $html .= <<<HTML
          <li class="p-3">
            <a href="{$link}" class="text-white">{$icon_html}</a>
          </li>
        HTML;
    }
    $html .= '</ul>';
    return $html;
  }

  public function render(): string
  {
    return <<<HTML
      <footer>
        <div class="footer-content flex footer-wrapper">
          <div class="flex-1">
            <div class="flex gap-3 mb-4 items-center">
                <div class="footer-content__logo rounded-full overflow-hidden">
                    <img class="h-full w-full object-contain" src="{$this->asset('img/faculty_logo.jpg')}" alt="Logo Khoa CNTT cua Truong CDKT Cao Thang">
                </div>
                <div class="flex flex-col justify-center">
                    <div class="text-xl text-white uppercase">KHOA CÔNG NGHỆ THÔNG TIN</div>
                    <div class="font-normal text-muted-foreground uppercase">TRƯỜNG CAO ĐẲNG KỸ THUẬT CAO THẮNG</div>
                </div>
            </div>
            <p class="text-footer text-sm">Lorem ipsum dolor sit amet consectetur adipisicing elit. Adipisci earum quisquam id quibusdam veniam. Libero omnis voluptate ipsam, consequuntur ad veniam, praesentium atque dicta repudiandae inventore recusandae placeat, fuga deleniti!</p>
          </div>

          {$this->render_link_list('Liên kết nhanh',$this->quick_links)}
          {$this->render_link_list('Chương trình đào tạo',$this->education_programs)}
          
          <div class="footer-content__item">
            {$this->render_contact_list('Liên hệ',$this->contact_infos)}
            {$this->render_social_links('Theo dõi chúng tôi',$this->social_links)}
          </div>
        </div>
        <p class="footer-wrapper text-footer text-sm">© 2025 Khoa Công nghệ Thông tin - Trường Cao Đẳng Kỹ Thuật Cao Thắng. All rights reserved.</p>
      </footer>
    HTML;
  }
}
