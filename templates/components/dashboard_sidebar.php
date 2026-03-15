<aside class="sidebar shadow sidebar--active" id="sidebar">
  <div class="sidebar__header">
    <a href="index.php" class="sidebar__brand flex gap-4">
      <div class="sidebar__logo object-contain">
        <img src="<?= url('/public/img/faculty_logo.jpg') ?>" alt="Logo Khoa CNTT cua Truong CDKT Cao Thang">
      </div>
      <div class="sidebar__title flex flex-col justify-center">
        <div class="sidebar_main-title font-medium text-lg uppercase">KHOA CNTT</div>
        <div class="sidebar_sub-title font-semibold uppercase">
          DASHBOARD
        </div>
      </div>
    </a>
  </div>

  <nav class="sidebar__nav">
    <ul class="sidebar__menu">
      <li class="sidebar__item sidebar__item--has-children">
        <a href="#0" class="sidebar__link" data-toggle="collapse" data-state="collapsed" data-target="#ddmenu_1"
          aria-expanded="false">
          <span class="sidebar__link-icon mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 640 640">
              <path
                d="M96 96C113.7 96 128 110.3 128 128L128 464C128 472.8 135.2 480 144 480L544 480C561.7 480 576 494.3 576 512C576 529.7 561.7 544 544 544L144 544C99.8 544 64 508.2 64 464L64 128C64 110.3 78.3 96 96 96zM208 288C225.7 288 240 302.3 240 320L240 384C240 401.7 225.7 416 208 416C190.3 416 176 401.7 176 384L176 320C176 302.3 190.3 288 208 288zM352 224L352 384C352 401.7 337.7 416 320 416C302.3 416 288 401.7 288 384L288 224C288 206.3 302.3 192 320 192C337.7 192 352 206.3 352 224zM432 256C449.7 256 464 270.3 464 288L464 384C464 401.7 449.7 416 432 416C414.3 416 400 401.7 400 384L400 288C400 270.3 414.3 256 432 256zM576 160L576 384C576 401.7 561.7 416 544 416C526.3 416 512 401.7 512 384L512 160C512 142.3 526.3 128 544 128C561.7 128 576 142.3 576 160z" />
            </svg>
          </span>
          <span class="sidebar__link-text">
            Bảng Điều Khiển
          </span>
          <div class="sidebar__link-cadet">
            <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round"></path>
            </svg>
          </div>
        </a>
        <ul id="ddmenu_1" class="sidebar__dropdown" data-state="collapsed">
          <li class="sidebar__dropdown-item">
            <a href="<?= url('admin/students') ?>" class="sidebar__dropdown-link">Quản Lý Sinh Viên</a>
          </li>
          <li class="sidebar__dropdown-item">
            <a href="<?= url('admin/teachers') ?>" class="sidebar__dropdown-link">Quản Lý Giảng viên</a>
          </li>
        </ul>
      </li>
      <div class="sidebar__divider">
      </div>
    </ul>
  </nav>
</aside>