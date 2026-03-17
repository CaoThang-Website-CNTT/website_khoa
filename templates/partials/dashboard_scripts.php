<!-- ========= All Javascript files linkup ======== -->
<script src="<?= url('/public/js/modal.js') ?>"></script>
<script src="<?= url('/public/js/dashboard.js') ?>"></script>
<script src="<?= url('/public/js/form.js') ?>"></script>
<script src="<?= url('/public/js/tabs.js') ?>"></script>
<script src="<?= url('/public/js/toast.js') ?>"></script>

<script>
  // Khởi tạo toast
  document.addEventListener('DOMContentLoaded', () => {
    window.toast = new Toast({ position: 'top-center', });
  });
</script>