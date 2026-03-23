<aside class="sidebar shadow sidebar--active" id="sidebar">
  <div class="sidebar__header">
    <a href="<?= url('admin') ?>" class="sidebar__brand flex gap-4">
      <div class="sidebar__logo object-contain">
        <img src="<?= url('/public/img/faculty_logo.jpg') ?>" alt="Logo Khoa CNTT">
      </div>
      <div class="sidebar__title flex flex-col justify-center">
        <div class="sidebar_main-title font-medium text-lg uppercase">KHOA CNTT</div>
        <div class="sidebar_sub-title font-semibold uppercase">DASHBOARD</div>
      </div>
    </a>
  </div>

  <nav class="sidebar__nav">
    <ul class="sidebar__menu">

      <!-- ── Tổng quan ─────────────────────────────────────────── -->
      <li class="sidebar__item">
        <a href="<?= url('admin') ?>" class="sidebar__link">
          <span class="sidebar__link-icon mr-2">
            <i class="fa-solid fa-house"></i>
          </span>
          <span class="sidebar__link-text">Tổng Quan</span>
        </a>
      </li>

      <div class="sidebar__divider"></div>

      <!-- ── Nội dung ───────────────────────────────────────────── -->
      <li class="sidebar__item sidebar__item--has-children">
        <a href="#0" class="sidebar__link" data-toggle="collapse" data-state="collapsed" data-target="#ddmenu_content"
          aria-expanded="false">
          <span class="sidebar__link-icon mr-2">
            <i class="fa-solid fa-list"></i>
          </span>
          <span class="sidebar__link-text">
            Nội Dung
          </span>
          <div class="sidebar__link-cadet">
            <i class="fa-solid fa-angle-down"></i>
          </div>
        </a>
        <ul id="ddmenu_content" class="sidebar__dropdown" data-state="collapsed">
          <li class="sidebar__dropdown-item">
            <a href="<?= url('admin/posts') ?>" class="sidebar__dropdown-link">Bài Viết</a>
          </li>
          <li class="sidebar__dropdown-item">
            <a href="<?= url('admin/sliders') ?>" class="sidebar__dropdown-link">Slider</a>
          </li>
          <li class="sidebar__dropdown-item">
            <a href="<?= url('admin/categories') ?>" class="sidebar__dropdown-link">Danh Mục</a>
          </li>
        </ul>
      </li>

      <div class="sidebar__divider"></div>

      <!-- ── Nhân sự ────────────────────────────────────────────── -->
      <li class="sidebar__item sidebar__item--has-children">
        <a href="#0" class="sidebar__link" data-toggle="collapse" data-state="collapsed" data-target="#ddmenu_people"
          aria-expanded="false">
          <span class="sidebar__link-icon mr-2">
            <i class="fa-solid fa-users"></i>
          </span>
          <span class="sidebar__link-text">
            Nhân Sự
          </span>
          <div class="sidebar__link-cadet">
            <i class="fa-solid fa-angle-down"></i>
          </div>
        </a>
        <ul id="ddmenu_people" class="sidebar__dropdown" data-state="collapsed">
          <li class="sidebar__dropdown-item">
            <a href="<?= url('admin/students') ?>" class="sidebar__dropdown-link">Sinh Viên</a>
          </li>
          <li class="sidebar__dropdown-item">
            <a href="<?= url('admin/teachers') ?>" class="sidebar__dropdown-link">Giảng Viên</a>
          </li>
        </ul>
      </li>

      <div class="sidebar__divider"></div>

      <!-- ── Giao diện ──────────────────────────────────────────── -->
      <li class="sidebar__item sidebar__item--has-children">
        <a href="#0" class="sidebar__link" data-toggle="collapse" data-state="collapsed"
          data-target="#ddmenu_appearance" aria-expanded="false">
          <span class="sidebar__link-icon mr-2">
            <i class="fa-solid fa-palette"></i>
          </span>
          <span class="sidebar__link-text">
            Giao Diện
          </span>
          <div class="sidebar__link-cadet">
            <i class="fa-solid fa-angle-down"></i>
          </div>
        </a>
        <ul id="ddmenu_appearance" class="sidebar__dropdown" data-state="collapsed">
          <li class="sidebar__dropdown-item">
            <a href="<?= url('admin/menus') ?>" class="sidebar__dropdown-link">Menu</a>
          </li>
        </ul>
      </li>

      <div class="sidebar__divider"></div>

      <!-- ── Cài đặt ────────────────────────────────────────────── -->
      <li class="sidebar__item sidebar__item--has-children">
        <a href="#0" class="sidebar__link" data-toggle="collapse" data-state="collapsed" data-target="#ddmenu_settings"
          aria-expanded="false">
          <span class="sidebar__link-icon mr-2">
            <i class="fa-solid fa-gear"></i>
          </span>
          <span class="sidebar__link-text">
            Cài Đặt
          </span>
          <div class="sidebar__link-cadet">
            <i class="fa-solid fa-angle-down"></i>
          </div>
        </a>
        <ul id="ddmenu_settings" class="sidebar__dropdown" data-state="collapsed">
          <li class="sidebar__dropdown-item">
            <a href="<?= url('admin/web_settings') ?>" class="sidebar__dropdown-link">Web Settings</a>
          </li>
        </ul>
      </li>
    </ul>
  </nav>
</aside>