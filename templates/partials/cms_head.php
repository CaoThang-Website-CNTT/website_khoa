<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CMS - Cao Thắng CNTT Dashboard</title>

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
<link rel="stylesheet" href="<?= url('public/css/landing.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/block_preview.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/block_editor.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/cms_page_editor.css') ?>">

<script src="<?= url('/public/js/toast.js') ?>"></script>
<script src="<?= url('/public/js/dnd.js') ?>"></script>

<script>
  window.PUBLIC_MEDIA_BASE = '<?= url('public/media') ?>';

  document.addEventListener('DOMContentLoaded', () => {
    window.toast = new Toast({ position: 'bottom-right' });
  });
</script>
