<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once BASE_PATH . '/templates/partials/canva_head.php'; ?>
  <?= $layout->yield('head'); ?>
</head>

<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= $flash['type'] ?>(
        "<?= htmlspecialchars($flash['title']) ?>",
        "<?= htmlspecialchars($flash['desc']) ?>"
      );
    });
  </script>
<?php endif; ?>

<body>
  <?php $helpSurface = 'block-editor'; include BASE_PATH . '/templates/components/editor_help_drawer.php'; ?>
  <!-- ======== Preloader =========== -->
  <?php include_once BASE_PATH . '/templates/components/preloader.php'; ?>
  <!-- ======== Preloader End =========== -->

  <!-- ========== Media Selector Modal =========== -->
  <?php require_once(BASE_PATH . '/templates/components/media_selector_modal.php'); ?>
  <!-- ========== Media Selector Modal End =========== -->

  <!-- ======== Canva Main Start =========== -->
  <main>
    <?= $layout->yield("content"); ?>
  </main>
  <!-- ======== Canva Main End =========== -->

  <!-- ========= Canva Scripts ======== -->
  <?php include_once BASE_PATH . '/templates/partials/canva_scripts.php'; ?>
  <?= $layout->yield('scripts'); ?>
  <?= $layout->yield('script'); ?>
  <!-- ========= Canva Scripts End ======== -->
</body>

</html>
