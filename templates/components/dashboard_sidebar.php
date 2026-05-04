<div class="sidebar__gap"></div>
<div class="sidebar__container">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar__header">
      <ul class="sidebar__menu">
        <li class="sidebar__menu-item">
          <a class="sidebar__menu-btn" href="<?= url('admin') ?>">
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
        <div class="sidebar__group">
          <div class="sidebar__group-label">Chức năng chính</div>
          <ul class="sidebar__menu">

            <!-- ── Tổng quan ─────────────────────────────────────────── -->
            <li class="sidebar__menu-item">
              <a class="sidebar__menu-btn" href="<?= url('admin') ?>">
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
      </nav>
    </div>

    <div class="sidebar__footer">
      Hello, User 123!
    </div>
  </aside>
</div>