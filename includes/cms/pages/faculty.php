<?php

$teachers = [
  ['name' => 'TS. Nguyễn Thị Lan', 'role' => 'Phó Giáo sư, Trí tuệ nhân tạo', 'phone' => '028 3821 2360', 'email' => 'lan.nguyen@faculty.edu.vn', 'portrait' => ['src' => 'public/img/about.jpg']],
  ['name' => 'ThS. Trần Minh Quân', 'role' => 'Giảng viên, Kỹ thuật phần mềm', 'phone' => '028 3821 2361', 'email' => 'quan.tran@faculty.edu.vn', 'portrait' => ['src' => 'public/img/about.jpg']],
  ['name' => 'TS. Lê Hoàng Anh', 'role' => 'Trưởng bộ môn, Khoa học dữ liệu', 'phone' => '028 3821 2362', 'email' => 'anh.le@faculty.edu.vn', 'portrait' => ['src' => 'public/img/about.jpg']],
  ['name' => 'ThS. Phạm Thu Hà', 'role' => 'Giảng viên, Hệ thống thông tin', 'phone' => '028 3821 2363', 'email' => 'ha.pham@faculty.edu.vn', 'portrait' => ['src' => 'public/img/about.jpg']],
  ['name' => 'TS. Võ Quốc Bảo', 'role' => 'Giảng viên chính, An toàn thông tin', 'phone' => '028 3821 2364', 'email' => 'bao.vo@faculty.edu.vn', 'portrait' => ['src' => 'public/img/about.jpg']],
  ['name' => 'ThS. Đặng Ngọc Mai', 'role' => 'Giảng viên, Mạng máy tính', 'phone' => '028 3821 2365', 'email' => 'mai.dang@faculty.edu.vn', 'portrait' => ['src' => 'public/img/about.jpg']],
];

return [
  'title' => 'Đội ngũ giảng viên',
  'slug' => 'faculty',
  'route_path' => '/giang-vien',
  'type' => 'faculty_page',
  'layout_mode' => 'section_schema',
  'sections' => [
    ['id' => 'breadcrumbs', 'type' => 'sections/breadcrumbs', 'label' => 'Điều hướng', 'locked' => true],
    [
      'id' => 'faculty_hero',
      'type' => 'sections/about_hero',
      'label' => 'Giới thiệu đội ngũ',
      'locked' => true,
      'data' => [
        'image' => 'public/img/about.jpg',
        'badge' => 'Đội ngũ giảng viên',
        'title' => 'Khoa Công nghệ Thông tin',
        'subtitle' => "Giàu kinh nghiệm, vững chuyên môn và luôn tiên phong đổi mới,\nđồng hành cùng sinh viên làm chủ công nghệ tương lai.",
      ],
    ],
    [
      'id' => 'teacher_directory',
      'type' => 'sections/teacher_directory',
      'label' => 'Danh sách giảng viên',
      'locked' => false,
      'data' => ['teachers' => $teachers],
      'repeaters' => [
        'teachers' => [
          'label' => 'Giảng viên',
          'min' => 1,
          'item' => ['name' => 'Giảng viên mới', 'role' => '', 'phone' => '', 'email' => '', 'portrait' => ['src' => 'public/img/about.jpg']],
        ],
      ],
    ],
  ],
];
