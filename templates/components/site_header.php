<?php
$headerMode = $headerMode ?? "full";
$email = htmlspecialchars($settings['contact_email'] ?? '');
$phone = htmlspecialchars($settings['contact_hotline'] ?? '');
$siteName = htmlspecialchars($settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin');
?>
<header class="site-header">

  <!-- BANNER: START -->
  <div class="banner hidden md:flex py-2">
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
<div class="main-header z-50 shadow">
  <div class="container">
    <div class="site-header__row flex justify-between items-center px-4 py-2">
      <a href="<?= url(""); ?>" class="site-header__brand flex gap-4">
        <div class="web-logo object-contain">
          <img src="<?= url('/public/img/faculty_logo.jpg') ?>" alt="Logo <?= $siteName ?>">
        </div>
        <div class="hidden md:flex flex-col justify-center">
          <div class="text-lg uppercase"><?= $siteName ?></div>
          <div class="uni-name text-sm uppercase">TRƯỜNG CAO ĐẲNG KỸ THUẬT CAO THẮNG</div>
        </div>
      </a>
      <?php if ($headerMode === "full"): ?>
        <div class="site-header__desktop-actions hidden md:flex items-center gap-4">
          <label class="search-bar flex items-center px-4 gap-2 rounded-3xl text-sm" data-variant="alt"
            for="search-input">
            <span class="search-bar__icon" aria-hidden="true">
              <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input class="search-bar__input" id="search-input" placeholder="Tìm kiếm..." autocomplete="off"
              autocorrect="off">
          </label>
        </div>
        <div class="site-header__mobile-actions flex md:hidden">
          <button class="site-header__icon-btn" type="button" data-mobile-search-toggle aria-controls="site-mobile-search"
            aria-expanded="false" aria-label="Mở tìm kiếm">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
          <button class="site-header__icon-btn" type="button" data-mobile-menu-toggle aria-controls="site-mobile-menu"
            aria-expanded="false" aria-label="Mở menu">
            <i class="fa-solid fa-bars"></i>
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($headerMode === "full") {
    require_once BASE_PATH . '/templates/components/mobile_search.php';
    renderMobileSearch();
  }
  ?>

  <?php if ($headerMode === "full") {
    require_once BASE_PATH . '/templates/components/mobile_menu.php';
    renderMobileMenu($headerMenu ?? []);
  }
  ?>

</div>
<!-- MAIN-HEADER: END -->
</header>

<?php if ($headerMode === "full") {
  require_once BASE_PATH . '/templates/components/site_nav.php';
  renderNav($headerMenu ?? null);
}
?>
