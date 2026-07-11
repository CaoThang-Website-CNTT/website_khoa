<!DOCTYPE html>
<html lang="en">

<head>
  <?php include_once BASE_PATH . '/templates/partials/dashboard_head.php'; ?>
  <?= $layout->yield("head"); ?>
</head>

<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= $flash['type'] ?>(
        "<?= htmlspecialchars($flash['title']) ?>",
        "<?= htmlspecialchars($flash['desc']) ?>"
      );
    });
  </script>
<?php endif; ?>

<body>
  <?php include_once BASE_PATH . '/templates/components/preloader.php'; ?>
  <div class="sidebar__wrapper">
    <div class="sidebar__portal">
      <div class="sidebar__overlay" data-state="closed" aria-hidden="true"></div>
      <?php include_once BASE_PATH . '/templates/components/dashboard_sidebar.php'; ?>
    </div>
    <main class="sidebar__inset">
      <?php include_once BASE_PATH . '/templates/components/dashboard_header.php'; ?>
      <div class="main-wrapper container container-fluid">
        <?= $layout->yield("banner") ?>

        <?php if ($layout->hasContent("heading") || $layout->hasContent("actions")): ?>
          <div class="title-wrapper mb-4">
            <div class="title-wrapper__content">
              <?= $layout->yield("heading") ?>
            </div>
            <?php if ($layout->hasContent("actions")): ?>
              <div class="title-wrapper__actions">
                <?= $layout->yield("actions") ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- Main: Start -->
        <?php if ($layout->hasContent("content")): ?> <!-- Dùng ViewEngine mới -->
          <?= $layout->yield("content") ?>
        <?php else: ?> <!-- Giữ lại cho tương thích với ViewEngine cũ -->
          <?= $content ?? '' ?>
        <?php endif; ?>
        <!-- Main: End -->
      </div>
    </main>
  </div>

  <?php include_once BASE_PATH . '/templates/partials/dashboard_scripts.php'; ?>
  <?= $layout->yield("scripts"); ?>
  <?= $layout->yield("script"); ?>
</body>

</html>
