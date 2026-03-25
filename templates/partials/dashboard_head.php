<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cao Thang CNTT Dashboard</title>

<!-- ========== All CSS files linkup ========= -->
<link rel="icon" type="image/png" sizes="32x32" href="public/favicon-32x32.png">
<link rel="preload" as="style" href="<?= url('public/css/fonts.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/base.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/common.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/modal.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/dashboard.css') ?>">
<link rel="preload" as="style" href="<?= url('public/css/table-manager.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/fontawesome.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/solid.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/regular.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fontawesome/brands.min.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/fonts.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/base.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/common.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/modal.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/dashboard.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/table-manager.css') ?>">


<link rel="shortcut icon" href="<?= url('assets/images/favicon.svg') ?>" type="image/x-icon" />

<script src="<?= url('/public/js/toast.js') ?>"></script>

<script>
// Khởi tạo toast
document.addEventListener('DOMContentLoaded', () => {
  window.toast = new Toast({
    position: 'bottom-right',
  });
});
</script>