<!-- Breadcrumb là thẻ nav -->
<!-- BreadcrumbList là thẻ ol -->
<!-- Tuân thủ theo aria và semantic -->
<?php
function renderBreadcrumb(array $links, string $separator = "<i class='fa-solid fa-chevron-right'></i>") {
  // Chuẩn hóa $links (đảm bảo chỉ có icon, url và title)
  foreach ($links as &$link) {
    if (!isset($link['icon'])) {
      $link['icon'] = null;
    }
    if (!isset($link['url'])) {
      $link['url'] = '#';
    }
    if (!isset($link['title'])) {
      $link['title'] = 'Không có tiêu đề';
    }
  }
  unset($link);

  $totalLinks = count($links);

  ?>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb__list">
      <?php foreach ($links as $key => $link): ?>
        <?php $isLast = ($key === $totalLinks - 1); ?>

        <li class="breadcrumb__item">
          <?php if (!$isLast): ?>
            <a href="<?= $link['url'] ?>" class="breadcrumb__link" aria-current="false">
              <?php if ($link['icon']): ?>
                <?= $link['icon'] ?>
              <?php endif; ?>
              <?= $link['title'] ?>
            </a>
          <?php else: ?>
            <span class="breadcrumb__page" role="link" aria-disabled="true" aria-current="page">
              <?php if ($link['icon']): ?>
                <?= $link['icon'] ?>
              <?php endif; ?>
              <?= $link['title'] ?>
            </span>
          <?php endif; ?>
        </li>
        <?php if (!$isLast): ?>
          <li class="breadcrumb__separator" role="presentation" aria-hidden="true">
            <?= $separator ?>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ol>
  </nav>
  <?php
};
?>