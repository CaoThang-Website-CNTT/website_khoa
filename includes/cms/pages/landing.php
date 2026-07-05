<?php
return [
  'title' => 'Trang chu', 'slug' => 'landing', 'route_path' => '/',
  'type' => 'landing_page', 'layout_mode' => 'section_schema',
  'sections' => [
    ['id' => 'hero', 'type' => 'sections/landing_hero', 'label' => 'Ảnh nổi bật', 'locked' => true],
    ['id' => 'landing_about', 'type' => 'sections/landing_about', 'label' => 'Giới thiệu khoa', 'locked' => false],
    ['id' => 'why_choose_us', 'type' => 'sections/why_choose_us', 'label' => 'Vì sao chọn chúng tôi', 'locked' => false],
    ['id' => 'stats', 'type' => 'sections/stats', 'label' => 'Thống kê', 'locked' => false],
    ['id' => 'partnerships', 'type' => 'sections/partnerships', 'label' => 'Đối tác', 'locked' => false],
    ['id' => 'newsfeed', 'type' => 'sections/newsfeed', 'label' => 'Tin tức', 'locked' => true],
  ],
];
