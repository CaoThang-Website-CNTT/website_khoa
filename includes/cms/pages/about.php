<?php
return [
  'title' => 'Gioi thieu', 'slug' => 'about', 'route_path' => '/gioi-thieu',
  'type' => 'landing_page', 'layout_mode' => 'section_schema',
  'sections' => [
    ['id' => 'breadcrumbs', 'type' => 'sections/breadcrumbs', 'label' => 'Điều hướng', 'locked' => true],
    ['id' => 'about_hero', 'type' => 'sections/about_hero', 'label' => 'Giới thiệu', 'locked' => false],
    ['id' => 'vision_mission', 'type' => 'sections/vision_mission', 'label' => 'Tầm nhìn và sứ mệnh', 'locked' => false],
    ['id' => 'history', 'type' => 'sections/history', 'label' => 'Lịch sử hình thành', 'locked' => false],
    ['id' => 'bento_grid', 'type' => 'sections/bento_grid', 'label' => 'Thông tin nổi bật', 'locked' => false],
  ],
];
