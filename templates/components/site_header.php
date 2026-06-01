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
        <a href="<?= url(""); ?>" class="flex gap-4">
          <div class="web-logo object-contain">
            <img src="<?= url('/public/img/faculty_logo.jpg') ?>" alt="Logo <?= $siteName ?>">
          </div>
          <div class="flex flex-col justify-center">
            <div class="text-xl uppercase"><?= $siteName ?></div>
            <div class="uni-name uppercase">TRƯỜNG CAO ĐẲNG KỸ THUẬT CAO THẮNG</div>
          </div>
        </a>
        <?php if ($headerMode === "full"): ?>
          <div class="flex items-center gap-4">
            <label class="search-bar flex items-center px-4 gap-2 rounded-3xl text-sm" data-variant="alt"
              for="search-input">
              <span class="search-bar__icon" aria-hidden="true">
                <i class="fa-solid fa-magnifying-glass"></i>
              </span>
              <input class="search-bar__input" id="search-input" placeholder="Tìm kiếm..." autocomplete="off"
                autocorrect="off">
            </label>
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