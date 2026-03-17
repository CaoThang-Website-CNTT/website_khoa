<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once __DIR__ . '/../partials/dashboard_head.php'; ?>
</head>

<body>
  <!-- ======== Preloader =========== -->
  <?php include_once __DIR__ . '/../components/preloader.php'; ?>
  <!-- ======== Preloader End =========== -->

  <!-- ======== Dashboard Sidebar =========== -->
  <?php include_once __DIR__ . '/../components/dashboard_sidebar.php'; ?>
  <!-- ======== Dashboard Sidebar End =========== -->

  <!-- ======== main-wrapper start =========== -->
  <main class="main-wrapper">
    <!-- ========== Dashboard Header ========== -->
    <?php include_once __DIR__ . '/../components/dashboard_header.php'; ?>
    <!-- ========== Dashboard Header End ========== -->

    <!-- Main content -->
    <section class="section">
      <div class="container container-fluid">
        <?= $content; ?>
      </div>
    </section>
    <!-- ========== Dashboard Footer =========== -->
    <?php include_once __DIR__ . '/../components/dashboard_footer.php'; ?>
    <!-- ========== Dashboard Footer End =========== -->
  </main>
  <?php include_once __DIR__ . '/../components/flash_alert.php'; ?>
  <!-- ======== main-wrapper end =========== -->

  <!-- ========= Dashboard Scripts ======== -->
  <?php include_once __DIR__ . '/../partials/dashboard_scripts.php'; ?>
  <!-- ========= Dashboard Scripts End ======== -->

</body>

</html>