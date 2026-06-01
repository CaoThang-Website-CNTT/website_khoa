<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once BASE_PATH . '/templates/partials/site_head.php'; ?>
  <?= $layout->yield("head"); ?>
</head>

<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= $flash['type'] ?> (
        "<?= htmlspecialchars($flash['title']) ?>",
        "<?= htmlspecialchars($flash['desc']) ?>"
      );
    });
  </script>
<?php endif; ?>

<body>
  <!-- ======== Preloader =========== -->
  <?php include_once BASE_PATH . '/templates/components/preloader.php'; ?>
  <!-- ======== Preloader End =========== -->

  <!-- HEADER: START -->
  <?php require_once BASE_PATH . '/templates/components/site_header.php'; ?>
  <!-- HEADER: END -->

  <main>
    <?= $content; ?>
  </main>

  <!-- FOOTER: START -->
  <?php require_once BASE_PATH . '/templates/components/site_footer.php'; ?>
  <!-- FOOTER: END -->

  <!-- ========= Site Scripts ======== -->
  <?php include_once BASE_PATH . '/templates/partials/site_scripts.php'; ?>
  <?= $layout->yield('scripts'); ?>
  <!-- ========= Site Scripts End ======== -->

</body>

</html>