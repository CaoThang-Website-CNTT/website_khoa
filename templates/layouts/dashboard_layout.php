<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once BASE_PATH . '/templates/partials/dashboard_head.php'; ?>
</head>

<body>
  <!-- ======== Preloader =========== -->
  <?php include_once BASE_PATH . '/templates/components/preloader.php'; ?>
  <!-- ======== Preloader End =========== -->

  <div class="sidebar__wrapper">
    <div class="sidebar__portal">
      <!-- ======== Dashboard Sidebar =========== -->
      <?php include_once BASE_PATH . '/templates/components/dashboard_sidebar.php'; ?>
      <!-- ======== Dashboard Sidebar End =========== -->
    </div>
    <main class="sidebar__inset">
      <!-- ========== Dashboard Header ========== -->
      <?php include_once BASE_PATH . '/templates/components/dashboard_header.php'; ?>
      <!-- ========== Dashboard Header End ========== -->

      <!-- ======== main-wrapper start =========== -->
      <div class="main-wrapper container container-fluid">
        <?= $content; ?>
      </div>
      <!-- ======== main-wrapper end =========== -->

      <!-- ========== Dashboard Footer =========== -->
      <?php include_once BASE_PATH . '/templates/components/dashboard_footer.php'; ?>
      <!-- ========== Dashboard Footer End =========== -->
    </main>
  </div>

  <!-- ========= Dashboard Scripts ======== -->
  <?php include_once BASE_PATH . '/templates/partials/dashboard_scripts.php'; ?>
  <!-- ========= Dashboard Scripts End ======== -->

</body>

</html>