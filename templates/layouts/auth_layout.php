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
  <?php
  $headerMode = "minimal";
  require_once BASE_PATH . '/templates/components/site_header.php';
  ?>
  <!-- HEADER: END -->

  <main>
    <div class="container py-16">
      <?= $content; ?>
    </div>
  </main>

  <!-- FOOTER: START -->
  <?php
  require_once BASE_PATH . '/templates/components/site_footer.php';
  ?>
  <!-- FOOTER: END -->


</body>

</html>