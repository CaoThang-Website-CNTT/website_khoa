<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once 'templates/partials/site_head.php'; ?>
</head>

<body>
  <!-- ======== Preloader =========== -->
  <?php include_once 'templates/components/preloader.php'; ?>
  <!-- ======== Preloader End =========== -->

  <!-- HEADER: START -->
  <?php require 'templates/components/site_header.php'; ?>
  <!-- HEADER: END -->

  <main>
    <?= $content; ?>
  </main>

  <!-- FOOTER: START -->
  <?php require 'templates/components/site_footer.php'; ?>
  <!-- FOOTER: END -->
</body>

</html>