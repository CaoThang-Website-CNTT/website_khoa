<?php
$_siteTitle = $settings['site_title'] ?? 'Khoa Công Nghệ Thông Tin';
$_metaTitle = $pageTitle ?? ($settings['seo.meta_title'] ?? $_siteTitle);
if (isset($pageTitle) && !str_contains($_metaTitle, ' | ')) {
  $_metaTitle = seo_title($pageTitle, $_siteTitle);
}
$_metaDesc = $pageDescription ?? ($settings['seo.meta_description'] ?? '');
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">

<title><?= htmlspecialchars($_metaTitle, ENT_QUOTES, 'UTF-8') ?></title>
<?php if (!empty($_metaDesc)): ?>
  <meta name="description" content="<?= htmlspecialchars($_metaDesc, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<link rel="canonical" href="<?= seo_canonical($pageCanonical ?? null) ?>">

<?php if (isset($pageSeo)): ?>
  <?= seo_og_tags($pageSeo) ?>
  <?= seo_twitter_tags($pageSeo) ?>
<?php endif; ?>

<?php if (isset($pageJsonLd)): ?>
  <?= seo_jsonld($pageJsonLd) ?>
<?php endif; ?>

<!-- ========== All CSS files linkup ========= -->
<link rel="icon" type="image/png" sizes="32x32" href="<?= url('public/favicon-32x32.png') ?>">
<link rel="preload" as="style" href="<?= url('public/css/fonts.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/common.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/base.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/main.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/fontawesome.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/solid.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/regular.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/brands.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fonts.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/base.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/common.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/main.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/block_preview.css') ?>">

<script src="<?= url('/public/js/toast.js') ?>"></script>

<script>
  // Khởi tạo toast
  document.addEventListener('DOMContentLoaded', () => {
    window.toast = new Toast({ position: 'bottom-right', });
  });
</script>
