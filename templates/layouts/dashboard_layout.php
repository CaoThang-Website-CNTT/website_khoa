<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once 'templates/partials/dashboard_head.php'; ?>
</head>

<body>
  <!-- ======== Preloader =========== -->
  <?php include_once 'templates/components/preloader.php'; ?>
  <!-- ======== Preloader End =========== -->

  <!-- ======== Dashboard Sidebar =========== -->
  <?php include_once 'templates/components/dashboard_sidebar.php'; ?>
  <!-- ======== Dashboard Sidebar End =========== -->

  <!-- ======== main-wrapper start =========== -->
  <main class="main-wrapper">
    <!-- ========== Dashboard Header ========== -->
    <?php include_once 'templates/components/dashboard_header.php'; ?>
    <!-- ========== Dashboard Header End ========== -->

    <!-- Main content -->
    <section class="section">
      <div class="container container-fluid">
        <?= $content; ?>
      </div>
    </section>

    <!-- ========== Dashboard Footer =========== -->
    <?php include_once 'templates/components/dashboard_footer.php'; ?>
    <!-- ========== Dashboard Footer End =========== -->
  </main>
  <!-- ======== main-wrapper end =========== -->

  <!-- ========= Dashboard Scripts ======== -->
  <?php include_once 'templates/partials/dashboard_scripts.php'; ?>
  <!-- ========= Dashboard Scripts End ======== -->
</body>

</html>