<?php
$authUser = request()->session()->authUser();
$role = $authUser['role'] ?? 'guest';
$currentPath = request()->path();
?>
<div class="sidebar__gap"></div>
<div class="sidebar__container">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar__header">
      <ul class="sidebar__menu">
        <li class="sidebar__menu-item">
          <a class="sidebar__menu-btn" href="<?= url($role === 'admin' ? 'admin' : 'student') ?>">
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
        <?php if ($role === 'admin'): ?>
        <!-- ── ADMIN MENU ─────────────────────────────────────────── -->
        <div class="sidebar__group">
          <div class="sidebar__group-label">Chức năng chính</div>
          <ul class="sidebar__menu">

            <!-- ── Tổng quan ─────────────────────────────────────────── -->
            <li class="sidebar__menu-item">
              <a class="sidebar__menu-btn <?= str_ends_with($currentPath, 'admin') ? 'active' : '' ?>"
                href="<?= url('admin') ?>">
                <i class="fa-solid fa-house"></i>
                Tổng Quan
              </a>
            </li>

            <!-- ── Nội dung ───────────────────────────────────────────── -->
            <li class="sidebar__menu-item">
              <div class="collapsible">
                <div class="sidebar__menu-btn">
                  <button class="collapsible__trigger">
                    <i class="fa-solid fa-list"></i>
                    Nội dung
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                </div>
                <div class="collapsible__content">
                  <ul class="sidebar__menu-sub">
                    <li class="sidebar__menu-sub-item">
                      <a href="<?= url('admin/posts/create') ?>" class="sidebar__menu-sub-item-btn">Bài Viết</a>
                    </li>
                    <li class="sidebar__menu-sub-item">
                      <a href="<?= url('admin/categories') ?>" class="sidebar__menu-sub-item-btn">Danh Mục</a>
                    </li>
                  </ul>
                </div>
              </div>
            </li>

            <!-- ── Nhân sự ────────────────────────────────────────────── -->
            <li class="sidebar__menu-item">
              <div class="collapsible">
                <div class="sidebar__menu-btn">
                  <button class="collapsible__trigger">
                    <i class="fa-solid fa-users"></i>
                    Nhân Sự
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                </div>
                <div class="collapsible__content">
                  <ul class="sidebar__menu-sub">
                    <li class="sidebar__menu-sub-item">
                      <a href="<?= url('admin/students') ?>" class="sidebar__menu-sub-item-btn">Sinh Viên</a>
                    </li>
                    <li class="sidebar__menu-sub-item">
                      <a href="<?= url('admin/classrooms') ?>" class="sidebar__menu-sub-item-btn">Lớp Học</a>
                    </li>
                    <li class="sidebar__menu-sub-item">
                      <a href="<?= url('admin/teachers') ?>" class="sidebar__menu-sub-item-btn">Giảng Viên</a>
                    </li>
                  </ul>
                </div>
              </div>
            </li>

            <!-- ── Thực tập ────────────────────────────────────────────── -->
            <li class="sidebar__menu-item">
              <a class="sidebar__menu-btn" href="<?= url('admin/internship_batches') ?>">
                <i class="fa-solid fa-house-laptop"></i>
                Thực tập tốt nghiệp
              </a>
            </li>
          </ul>
        </div>

        <div class="sidebar__group">
          <div class="sidebar__group-label">Hệ Thống</div>
          <ul class="sidebar__menu">
            <!-- ── Giao diện ──────────────────────────────────────────── -->
            <li class="sidebar__menu-item">
              <div class="collapsible">
                <div class="sidebar__menu-btn">
                  <button class="collapsible__trigger">
                    <i class="fa-solid fa-palette"></i>
                    Giao Diện
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                </div>
                <div class="collapsible__content">
                  <ul class="sidebar__menu-sub">
                    <li class="sidebar__menu-sub-item">
                      <a href="<?= url('admin/menus') ?>" class="sidebar__menu-sub-item-btn">Menu</a>
                    </li>
                    <li class="sidebar__menu-sub-item">
                      <a href="<?= url('admin/carousels') ?>" class="sidebar__menu-sub-item-btn">Carousel</a>
                    </li>
                  </ul>
                </div>
              </div>
            </li>

            <!-- ── Cài đặt ────────────────────────────────────────────── -->
            <li class="sidebar__menu-item">
              <div class="collapsible">
                <div class="sidebar__menu-btn">
                  <button class="collapsible__trigger">
                    <i class="fa-solid fa-gear"></i>
                    Cài Đặt
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                </div>
                <div class="collapsible__content">
                  <ul class="sidebar__menu-sub">
                    <li class="sidebar__menu-sub-item">
                      <a href="<?= url('admin/web_settings') ?>" class="sidebar__menu-sub-item-btn">Web Settings</a>
                    </li>
                  </ul>
                </div>
              </div>
            </li>
          </ul>
        </div>

        <?php elseif ($role === 'student'): ?>
          <!-- ── STUDENT MENU ───────────────────────────────────────── -->
          <div class="sidebar__group">
            <div class="sidebar__group-label">Cá nhân</div>
            <ul class="sidebar__menu">
              <li class="sidebar__menu-item">
                <a class="sidebar__menu-btn <?= str_contains($currentPath, 'student') ? 'active' : '' ?>"
                  href="<?= url('student') ?>">
                  <i class="fa-solid fa-user"></i>
                  Tổng Quan
                </a>
              </li>
              <li class="sidebar__menu-item">
                <div class="collapsible">
                  <div class="sidebar__menu-btn">
                    <button class="collapsible__trigger">
                      <i class="fa-solid fa-briefcase"></i>
                      Thực tập tốt nghiệp
                      <i class="fa-solid fa-angle-down"></i>
                    </button>
                  </div>
                  <div class="collapsible__content">
                    <ul class="sidebar__menu-sub">
                      <li class="sidebar__menu-sub-item">
                        <a href="<?= url('student/internship') ?>" class="sidebar__menu-sub-item-btn">Đợt thực tập</a>
                      </li>
                      <li class="sidebar__menu-sub-item">
                        <a href="<?= url('student/referral_letter') ?>" class="sidebar__menu-sub-item-btn">Giấy giới
                          thiệu</a>
                      </li>
                    </ul>
                  </div>
                </div>
              </li>
              <li class="sidebar__menu-item">
                <a class="sidebar__menu-btn <?= str_contains($currentPath, 'student/graduation') ? 'active' : '' ?>"
                  href="<?= url('student/graduation') ?>">
                  <i class="fa-solid fa-graduation-cap"></i>
                  Đồ Án Tốt Nghiệp
                </a>
              </li>
            </ul>
          </div>
        <?php endif; ?>

        <!-- ── CHUNG ────────────────────────────────────────────────── -->
        <div class="sidebar__group">
          <div class="sidebar__group-label">Tài khoản</div>
          <ul class="sidebar__menu">
            <li class="sidebar__menu-item">
              <a class="sidebar__menu-btn" href="<?= url('logout') ?>" style="color: var(--destructive);">
                <i class="fa-solid fa-right-from-bracket"></i>
                Đăng xuất
              </a>
            </li>
          </ul>
        </div>
      </nav>
    </div>

    <div class="sidebar__footer">
      <div class="sidebar__user-info">
        <?= htmlspecialchars($authUser['email'] ?? 'Guest') ?>
      </div>
    </div>
  </aside>
</div>