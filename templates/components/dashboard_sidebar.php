<?php
$authUser = request()->session()->authUser();
$role = $authUser['role'] ?? 'admin';
$currentPath = request()->path();

// Định nghĩa cấu trúc menu theo từng Role
$menuConfig = [
  'admin' => [
    [
      'group_label' => 'Chức năng chính',
      'items' => [
        [
          'type' => 'link',
          'label' => 'Tổng Quan',
          'icon' => 'fa-solid fa-house',
          'url' => 'admin',
          'active' => str_ends_with($currentPath, 'admin')
        ],
        [
          'type' => 'link',
          'label' => 'Người Dùng',
          'icon' => 'fa-solid fa-user-group',
          'url' => 'admin/accounts',
        ],
        [
          'type' => 'collapsible',
          'label' => 'Nội dung',
          'icon' => 'fa-solid fa-list',
          'children' => [
            ['label' => 'Bài Viết', 'url' => 'admin/posts'],
            ['label' => 'Danh Mục', 'url' => 'admin/categories'],
            ['label' => 'Thư Viện', 'url' => 'admin/media'],
          ]
        ],
        [
          'type' => 'collapsible',
          'label' => 'Thực tập tốt nghiệp',
          'icon' => 'fa-solid fa-house-laptop',
          'children' => [
            ['label' => 'Quản lý đợt', 'url' => 'admin/internship_batches'],
            ['label' => 'Quản lý công ty', 'url' => 'admin/companies'],
          ]
        ]
      ]
    ],
    [
      'group_label' => 'Hệ Thống',
      'items' => [
        [
          'type' => 'collapsible',
          'label' => 'Giao Diện',
          'icon' => 'fa-solid fa-palette',
          'children' => [
            ['label' => 'Menu', 'url' => 'admin/menus'],
            ['label' => 'Carousel', 'url' => 'admin/carousels'],
          ]
        ],
        [
          'type' => 'collapsible',
          'label' => 'Cài Đặt',
          'icon' => 'fa-solid fa-gear',
          'children' => [
            ['label' => 'Web Settings', 'url' => 'admin/web_settings'],
            ['label' => 'Tickets', 'url' => 'admin/tickets'],
          ]
        ],
        [
          'type' => 'link',
          'label' => 'Phản hồi',
          'icon' => 'fa-solid fa-circle-question',
          'url' => 'admin/tickets/create'
        ]
      ]
    ]
  ],
  'teacher' => [
    [
      'group_label' => 'Cá nhân',
      'items' => [
        [
          'type' => 'link',
          'label' => 'Tổng Quan',
          'icon' => 'fa-solid fa-user',
          'url' => 'teacher',
          'active' => str_contains($currentPath, 'teacher') && !str_contains($currentPath, 'teacher/internship_batches')
        ],
        [
          'type' => 'link',
          'label' => 'Thực tập tốt nghiệp',
          'icon' => 'fa-solid fa-briefcase',
          'url' => 'teacher/internship_batches',
          'active' => str_contains($currentPath, 'teacher/internship_batches')
        ]
      ]
    ]
  ],
  'student' => [
    [
      'group_label' => 'Cá nhân',
      'items' => [
        [
          'type' => 'link',
          'label' => 'Tổng Quan',
          'icon' => 'fa-solid fa-user',
          'url' => 'student',
          'active' => str_contains($currentPath, 'student') && !str_contains($currentPath, 'student/internship') && !str_contains($currentPath, 'student/graduation')
        ],
        [
          'type' => 'collapsible',
          'label' => 'Thực tập tốt nghiệp',
          'icon' => 'fa-solid fa-briefcase',
          'children' => [
            ['label' => 'Đợt thực tập', 'url' => 'student/internship'],
            ['label' => 'Giấy giới thiệu', 'url' => 'student/referral_letter'],
          ]
        ],
        [
          'type' => 'link',
          'label' => 'Đồ Án Tốt Nghiệp',
          'icon' => 'fa-solid fa-graduation-cap',
          'url' => 'student/graduation',
          'active' => str_contains($currentPath, 'student/graduation')
        ]
      ]
    ]
  ]
];

// Lấy danh sách group menu theo role hiện tại, mặc định mảng rỗng nếu không khớp role
$currentGroups = $menuConfig[$role] ?? [];
?>

<div class="sidebar__gap"></div>
<div class="sidebar__container">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar__header">
      <ul class="sidebar__menu">
        <li class="sidebar__menu-item">
          <a class="sidebar__menu-btn" href="<?= url($role) ?>">
            <div class="sidebar__logo object-contain">
              <img src="<?= url('/public/img/faculty_logo.jpg') ?>" alt="Logo Khoa CNTT">
            </div>
            <div class="sidebar__title flex flex-col justify-center">
              <div class="sidebar_main-title font-medium text-lg uppercase">KHOA CNTT</div>
              <div class="sidebar_sub-title font-semibold uppercase">DASHBOARD</div>
            </div>
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar__content">
      <nav class="sidebar__nav">

        <?php foreach ($currentGroups as $group): ?>
          <div class="sidebar__group">
            <div class="sidebar__group-label">
              <?= htmlspecialchars($group['group_label']) ?>
            </div>
            <ul class="sidebar__menu">

              <?php foreach ($group['items'] as $item): ?>

                <?php if ($item['type'] === 'link'): ?>
                  <li class="sidebar__menu-item">
                    <a class="sidebar__menu-btn <?= ($item['active'] ?? false) ? 'active' : '' ?>"
                      href="<?= url($item['url']) ?>">
                      <i class="<?= $item['icon'] ?>"></i>
                      <?= htmlspecialchars($item['label']) ?>
                    </a>
                  </li>

                <?php elseif ($item['type'] === 'collapsible'): ?>
                  <li class="sidebar__menu-item">
                    <div class="collapsible">
                      <div class="sidebar__menu-btn">
                        <button class="collapsible__trigger">
                          <i class="<?= $item['icon'] ?>"></i>
                          <?= htmlspecialchars($item['label']) ?>
                          <i class="fa-solid fa-angle-down"></i>
                        </button>
                      </div>
                      <div class="collapsible__content">
                        <ul class="sidebar__menu-sub">
                          <?php foreach ($item['children'] as $child): ?>
                            <li class="sidebar__menu-sub-item">
                              <a href="<?= url($child['url']) ?>" class="sidebar__menu-sub-item-btn">
                                <?= htmlspecialchars($child['label']) ?>
                              </a>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      </div>
                    </div>
                  </li>
                <?php endif; ?>

              <?php endforeach; ?>

            </ul>
          </div>
        <?php endforeach; ?>

      </nav>
    </div>

    <div class="sidebar__footer">
      <ul class="sidebar__menu">
        <li class="sidebar__menu-item">
          <div class="sidebar__menu-btn">
            <?php
            $avatarComp = BASE_PATH . '/templates/components/user_avatar.php';
            if (file_exists($avatarComp)) {
              include $avatarComp;
            } else {
              echo '<a href="' . url('logout') . '" class="sidebar__menu-btn">
              <i class="fa-solid fa-right-from-bracket"></i>
              Đăng xuất
              </a>';
            }
            ?>
          </div>
        </li>
      </ul>
    </div>
  </aside>
</div>