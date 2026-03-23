<?php
// $settings được extract từ render() — available tự động
// Fallback cho từng key đề phòng DB chưa seed hoặc setting bị xóa
$email = htmlspecialchars($settings['contact.email'] ?? '');
$phone = htmlspecialchars($settings['contact.phone'] ?? '');
$siteName = htmlspecialchars($settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin');
?>
<header class="z-50">

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
        <div class="search-bar flex items-center px-4 gap-2 rounded-3xl text-sm">
          <i class="fa-brands fa-sistrix"></i>
          <input class="search-bar__input" placeholder="Tìm kiếm..." autocomplete="off" autocorrect="off">
        </div>
      </div>
    </div>

    <?php
    require_once BASE_PATH . '/templates/components/site_nav.php';
    renderNav($menu);
    ?>

  </div>
  <!-- MAIN-HEADER: END -->
</header>