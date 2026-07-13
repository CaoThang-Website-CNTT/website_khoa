<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once BASE_PATH . '/templates/partials/site_head.php'; ?>
  <?= $layout->yield("head"); ?>
</head>

<?php if ($flash = request()->session()->getFlash('notification')): ?>
  <?php
  $flashType = (string) ($flash['type'] ?? 'info');
  $toastMethod = match ($flashType) {
    'success' => 'success',
    'error', 'danger' => 'error',
    'warning' => 'warn',
    default => 'info',
  };
  $toastJsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
  ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.toast?.[<?= json_encode($toastMethod, $toastJsonFlags) ?>]?.(
        <?= json_encode((string) ($flash['title'] ?? ''), $toastJsonFlags) ?>,
        <?= json_encode((string) ($flash['desc'] ?? ''), $toastJsonFlags) ?>
      );
    });
  </script>
<?php endif; ?>

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

  <!-- ========= Site Scripts ======== -->
  <?php include_once BASE_PATH . '/templates/partials/site_scripts.php'; ?>
  <?= $layout->yield("scripts"); ?>
  <!-- ========= Site Scripts End ======== -->

</body>

</html>
