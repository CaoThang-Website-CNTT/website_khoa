<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once BASE_PATH . '/templates/partials/canva_head.php'; ?>
  <?= $layout->yield('head'); ?>
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
