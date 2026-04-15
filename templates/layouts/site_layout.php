<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once BASE_PATH . '/templates/partials/site_head.php'; ?>
</head>

<body>
  <!-- ======== Preloader =========== -->
  <?php include_once BASE_PATH . '/templates/components/preloader.php'; ?>
  <!-- ======== Preloader End =========== -->

  <!-- HEADER: START -->
  <?php require_once BASE_PATH . '/templates/components/site_header.php'; ?>
  <!-- HEADER: END -->

  <main>
    <?php if (isset($breadcrumb) && isset($breadcrumb['items'])): ?>
      <?php include_once BASE_PATH . '/templates/components/breadcrumb.php'; ?>
    <?php endif; ?>
    <?= $content; ?>
  </main>

  <!-- FOOTER: START -->
  <?php require_once BASE_PATH . '/templates/components/site_footer.php'; ?>
  <!-- FOOTER: END -->

  <!-- ========= Site Scripts ======== -->
  <?php include_once BASE_PATH . '/templates/partials/site_scripts.php'; ?>
  <!-- ========= Site Scripts End ======== -->

</body>

</html>