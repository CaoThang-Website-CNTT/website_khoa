<?php

use App\Cms\EducationPageDefaults;

return [
  'title' => 'Dao tao', 'slug' => 'education', 'route_path' => '/dao-tao',
  'type' => 'education_page', 'layout_mode' => 'section_schema',
  'sections' => [
    ['id' => 'education_hub', 'type' => 'sections/education_hub', 'label' => 'Tổng quan đào tạo', 'locked' => false, 'data' => EducationPageDefaults::hub()],
    ['id' => 'admissions', 'type' => 'sections/admissions', 'label' => 'Thông tin tuyển sinh', 'locked' => false, 'data' => EducationPageDefaults::admissions()],
    ['id' => 'programs', 'type' => 'sections/programs', 'label' => 'Chương trình đào tạo', 'locked' => false, 'data' => EducationPageDefaults::programsSection()],
    ['id' => 'outcomes', 'type' => 'sections/outcomes', 'label' => 'Chuẩn đầu ra', 'locked' => false, 'data' => EducationPageDefaults::outcomes()],
    ['id' => 'curriculum', 'type' => 'sections/curriculum', 'label' => 'Danh sách môn học', 'locked' => false, 'data' => EducationPageDefaults::curriculum()],
  ],
];
