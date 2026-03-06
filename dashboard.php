<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon" />
  <title>Cao Thang CNTT Dashboard</title>

  <!-- ========== All CSS files linkup ========= -->
  <link rel="stylesheet" href="public/css/base.css" />
  <link rel="stylesheet" href="public/css/common.css" />
  <link rel="stylesheet" href="public/css/dashboard.css" />
</head>

<body>
  <!-- ======== Preloader =========== -->
  <?php include_once 'includes/preloader.php'; ?>
  <!-- ======== Preloader End =========== -->

  <!-- ======== Dashboard Sidebar =========== -->
  <?php include_once 'includes/dashboard_sidebar.php'; ?>
  <!-- ======== Dashboard Sidebar End =========== -->

  <!-- ======== main-wrapper start =========== -->
  <main class="main-wrapper">
    <!-- ========== Dashboard Header ========== -->
    <?php include_once 'includes/dashboard_header.php'; ?>
    <!-- ========== Dashboard Header End ========== -->

    <!-- Main content removed; keeping an empty section -->
    <section class="section">
      <div class="container-fluid">
        <!-- main content intentionally removed -->
      </div>
    </section>

    <!-- ========== Dashboard Footer =========== -->
    <?php include_once 'includes/dashboard_footer.php'; ?>
    <!-- ========== Dashboard Footer End =========== -->
  </main>
  <!-- ======== main-wrapper end =========== -->

  <!-- ========= Dashboard Scripts ======== -->
  <?php include_once 'includes/dashboard_scripts.php'; ?>
  <!-- ========= Dashboard Scripts End ======== -->
</body>

</html>