<?php
$_siteTitle = $settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin';
$_seoHead = [
  'siteTitle' => $_siteTitle,
  'title' => $pageTitle ?? ($settings['seo.meta_title'] ?? $_siteTitle),
  'description' => $pageDescription ?? ($settings['seo.meta_description'] ?? ''),
  'canonical' => $pageCanonical ?? null,
  'meta' => $pageSeo ?? [],
  'jsonld' => $pageJsonLd ?? [],
];
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">

<?= seo_head($_seoHead) ?>

<!-- ========== All CSS files linkup ========= -->
<link rel="icon" type="image/png" sizes="32x32" href="<?= url('public/favicon-32x32.png') ?>">
<link rel="preload" as="style" href="<?= url('public/css/fonts.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/common.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/base.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/main.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/landing.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/fontawesome.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/solid.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/regular.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/brands.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fonts.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/base.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/common.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/main.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/landing.css') ?>?v=<?= filemtime(BASE_PATH . '/public/css/landing.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/block_preview.css') ?>">

<script src="<?= url('/public/js/toast.js') ?>"></script>

<script>
  // Khởi tạo toast
  document.addEventListener('DOMContentLoaded', () => {
    window.toast = new Toast({ position: 'bottom-right', });
  });
</script>
