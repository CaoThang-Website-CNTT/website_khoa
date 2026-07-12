<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once BASE_PATH . '/templates/partials/cms_head.php'; ?>
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
  <?php $helpSurface = 'cms'; include BASE_PATH . '/templates/components/editor_help_drawer.php'; ?>
  <?php include_once BASE_PATH . '/templates/components/preloader.php'; ?>
  <?php require_once BASE_PATH . '/templates/components/media_selector_modal.php'; ?>

  <main class="cms-editor-shell">
    <div id="be-topbar">
      <div id="be-topbar-left">
        <?= $layout->yield('topbar_left'); ?>
      </div>

      <div id="be-topbar-center">
        <?= $layout->yield('topbar_center'); ?>
      </div>

      <div id="be-topbar-right">
        <?= $layout->yield('topbar_right'); ?>
      </div>
    </div>

    <div id="be-body">
      <div class="be-panel__wrapper">
        <div class="be-panel__gap"></div>
        <?= $layout->yield('left_panel'); ?>
      </div>

      <div id="be-canvas-wrap">
        <div id="be-canvas">
          <div class="be-canvas__content-wrapper">
            <div class="be-canvas__content">
              <?= $layout->yield('canvas'); ?>
            </div>
          </div>
        </div>
      </div>

      <div class="be-panel__wrapper">
        <div class="be-panel__gap"></div>
        <?= $layout->yield('right_panel'); ?>
      </div>
    </div>

    <?= $layout->yield('content'); ?>
  </main>

  <?php include_once BASE_PATH . '/templates/partials/cms_scripts.php'; ?>
  <?= $layout->yield('scripts'); ?>
  <?= $layout->yield('script'); ?>
</body>

</html>
