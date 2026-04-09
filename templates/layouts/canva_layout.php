<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once BASE_PATH . '/templates/partials/canva_head.php'; ?>
</head>

<body>
  <!-- ======== Preloader =========== -->
  <?php include_once BASE_PATH . '/templates/components/preloader.php'; ?>
  <!-- ======== Preloader End =========== -->
  <main>
    <!-- ========== Dashboard Header ========== -->
    <?php include_once BASE_PATH . '/templates/components/canva_header.php'; ?>
    <!-- ========== Dashboard Header End ========== -->

    <!-- ======== main start =========== -->
    <?= $content; ?>
    <!-- ======== main end =========== -->
  </main>
  <!-- ========= Dashboard Scripts ======== -->
  <?php include_once BASE_PATH . '/templates/partials/canva_scripts.php'; ?>
  <!-- ========= Dashboard Scripts End ======== -->

</body>

</html>