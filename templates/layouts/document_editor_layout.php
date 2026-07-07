<!DOCTYPE html>
<html lang="vi">
<head>
  <?php include BASE_PATH . '/templates/partials/document_editor_head.php'; ?>
  <?= $layout->yield('head') ?>
</head>
<body>
  <main class="document-editor-shell">
    <div id="be-topbar">
      <div id="be-topbar-left"><?= $layout->yield('topbar_left') ?></div>
      <div id="be-topbar-center"><?= $layout->yield('topbar_center') ?></div>
      <div id="be-topbar-right"><?= $layout->yield('topbar_right') ?></div>
    </div>

    <div id="be-body">
      <div id="be-canvas-wrap">
        <div id="be-canvas">
          <div class="be-canvas__content-wrapper">
            <div class="be-canvas__content"><?= $layout->yield('canvas') ?></div>
          </div>
        </div>
      </div>

      <?php if ($layout->hasContent('right_panel')): ?>
        <div class="be-panel__wrapper">
          <div class="be-panel__gap"></div>
          <?= $layout->yield('right_panel') ?>
        </div>
      <?php endif; ?>
    </div>

    <?= $layout->yield('content') ?>
  </main>
  <?php include BASE_PATH . '/templates/partials/document_editor_scripts.php'; ?>
  <?= $layout->yield('scripts') ?>
</body>
</html>
