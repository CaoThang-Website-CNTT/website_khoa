<?php
$statusCode = (int) ($statusCode ?? 500);
$statusTitle = (string) ($statusTitle ?? 'Đã có lỗi xảy ra');
$statusDescription = (string) ($statusDescription ?? 'Vui lòng thử lại sau.');
$statusHomeUrl = htmlspecialchars(url(''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($statusCode . ' - ' . $statusTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars(url('public/favicon-32x32.png'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="<?= htmlspecialchars(url('public/css/fontawesome/fontawesome.min.css'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="<?= htmlspecialchars(url('public/css/fontawesome/solid.min.css'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="<?= htmlspecialchars(url('public/css/fonts.css'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="<?= htmlspecialchars(url('public/css/base.css'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="<?= htmlspecialchars(url('public/css/common.css'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="<?= htmlspecialchars(url('public/css/status_pages.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body>
  <main class="status-page">
    <section class="card status-page__card" aria-labelledby="status-title">
      <div class="status-page__content">
        <span class="status-page__code"><?= htmlspecialchars((string) $statusCode, ENT_QUOTES, 'UTF-8') ?></span>
        <h1 class="status-page__title" id="status-title">
          <?= htmlspecialchars($statusTitle, ENT_QUOTES, 'UTF-8') ?>
        </h1>
        <p class="status-page__description">
          <?= htmlspecialchars($statusDescription, ENT_QUOTES, 'UTF-8') ?>
        </p>
        <div class="status-page__actions">
          <button class="btn" data-variant="outline" data-size="lg" type="button" onclick="history.length > 1 ? history.back() : location.assign('<?= $statusHomeUrl ?>')">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Quay lại
          </button>
          <a class="btn" data-variant="primary" data-size="lg" href="<?= $statusHomeUrl ?>">
            <i class="fa-solid fa-house" aria-hidden="true"></i>
            Về trang chủ
          </a>
        </div>
      </div>
    </section>
  </main>
</body>

</html>
