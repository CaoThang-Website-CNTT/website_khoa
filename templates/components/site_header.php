<?php
$headerMode = $headerMode ?? "full";
$email = htmlspecialchars($settings['contact_email'] ?? '');
$phone = htmlspecialchars($settings['contact_hotline'] ?? '');
$siteName = htmlspecialchars($settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin');
?>
<header class="z-50 shadow">

  <!-- BANNER: START -->
  <div class="banner py-2">
    <div class="container flex gap-4 px-4 font-light">

      <?php if ($email): ?>
      <div class="flex items-center gap-1">
        <i class="fa-solid fa-envelope"></i>
        <?= $email ?>
      </div>
      <?php endif; ?>

      <?php if ($phone): ?>
      <div class="flex items-center gap-1">
        <i class="fa-solid fa-phone"></i>
        <?= $phone ?>
      </div>
      <?php endif; ?>

    </div>
  </div>
  <!-- BANNER: END -->

  <!-- MAIN-HEADER: START -->
  <div class="main-header">
    <div class="container">
      <div class="flex justify-between items-center p-4">
        <div class="flex gap-4">
          <div class="web-logo object-contain">
            <img src="<?= url('/public/img/faculty_logo.jpg') ?>" alt="Logo <?= $siteName ?>">
          </div>
          <div class="flex flex-col justify-center">
            <div class="text-xl uppercase"><?= $siteName ?></div>
            <div class="uni-name uppercase">TRƯỜNG CAO ĐẲNG KỸ THUẬT CAO THẮNG</div>
          </div>
        </div>
        <?php if ($headerMode === "full"): ?>
        <div class="flex items-center gap-4">
          <div class="search-bar flex items-center px-4 gap-2 rounded-3xl text-sm">
            <i class="fa-brands fa-sistrix"></i>
            <input class="search-bar__input" placeholder="Tìm kiếm..." autocomplete="off" autocorrect="off">
          </div>

          <?php $authUser = request()->session()->authUser(); ?>
          <?php if ($authUser): ?>
          <div class="user-menu" id="userMenu">
            <button class="user-menu__trigger" aria-haspopup="true" aria-expanded="false">
              <div class="user-menu__avatar">
                <i class="fa-solid fa-user"></i>
              </div>
              <span class="user-menu__name"><?= htmlspecialchars($authUser['email']) ?></span>
              <i class="fa-solid fa-chevron-down user-menu__icon"></i>
            </button>
            <div class="user-menu__dropdown" id="userDropdown" role="menu" aria-orientation="vertical">
              <?php if ($authUser['role'] === 'student'): ?>
              <a href="<?= url('/student') ?>" class="user-menu__item" role="menuitem">
                <i class="fa-solid fa-gauge-high"></i>
                Dashboard
              </a>
              <?php elseif ($authUser['role'] === 'teacher'): ?>
              <a href="<?= url('/teacher') ?>" class="user-menu__item" role="menuitem">
                <i class="fa-solid fa-gauge-high"></i>
                Dashboard
              </a>
              <?php elseif ($authUser['role'] === 'admin'): ?>
              <a href="<?= url('/admin') ?>" class="user-menu__item" role="menuitem">
                <i class="fa-solid fa-gauge-high"></i>
                Admin Dashboard
              </a>
              <?php endif; ?>
              <div class="user-menu__separator"></div>
              <a href="<?= url('/logout') ?>" class="user-menu__item user-menu__item--destructive" role="menuitem">
                <i class="fa-solid fa-right-from-bracket"></i>
                Đăng xuất
              </a>
            </div>
          </div>

          <script>
          document.addEventListener('DOMContentLoaded', () => {
            const trigger = document.querySelector('.user-menu__trigger');
            const dropdown = document.getElementById('userDropdown');

            if (trigger && dropdown) {
              const toggle = (force) => {
                const isOpen = force !== undefined ? force : dropdown.getAttribute('data-state') === 'open';
                const newState = !isOpen ? 'open' : 'closed';
                dropdown.setAttribute('data-state', newState);
                trigger.setAttribute('aria-expanded', !isOpen);
              };

              trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                toggle();
              });

              document.addEventListener('click', () => toggle(false));

              dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
              });
            }
          });
          </script>
          <?php else: ?>
          <a href="<?= url('/login') ?>" class="btn rounded-3xl" data-variant="primary" data-size="lg">
            Đăng nhập
          </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($headerMode === "full") {
      require_once BASE_PATH . '/templates/components/site_nav.php';
      renderNav($headerMenu ?? null);
    }
    ?>

  </div>
  <!-- MAIN-HEADER: END -->
</header>