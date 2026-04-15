<?php

/**
 * Cấu trúc breadcrumb
 * [
 *   'items' => [
 *     ['label' => 'Trang chủ', 'url' => '/'],
 *     ['label' => 'Giới Thiệu', 'active' => true],
 *   ],
 *   'schema' => true, //optional, để bật schema.org microdata
 * ]
 */

if (!isset($breadcrumb) || !isset($breadcrumb['items']) || !is_array($breadcrumb['items'])) {
  return;
}

$items = $breadcrumb['items'];
$enableSchema = !empty($breadcrumb['schema']);
$count = count($items);
?>
<nav class="breadcrumb" <?= $enableSchema ? 'aria-label="Breadcrumb"' : '' ?>>
  <div class="container-wrapper py-4 flex items-center gap-2">
    <?php if ($enableSchema): ?>
      <ol class="breadcrumb__list flex items-center gap-2" itemscope itemtype="https://schema.org/BreadcrumbList">
        <?php foreach ($items as $index => $item): ?>
          <?php $position = $index + 1; ?>
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="flex items-center gap-1">
            <?php if (isset($item['url']) && isset($item['active']) && !$item['active']): ?>
              <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>" itemprop="item" class="breadcrumb__link flex items-center gap-1">
                <?php if ($index === 0): ?>
                  <div class="breadcrumb__icon-wrapper flex items-center justify-center">
                    <i class="fa-regular fa-house"></i>
                  </div>
                <?php endif; ?>
                <span itemprop="name" class="breadcrumb__text text-sm"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
              </a>
            <?php else: ?>
              <span class="breadcrumb__link breadcrumb__link--active flex items-center font-medium" aria-current="page">
                <span itemprop="name" class="breadcrumb__text text-sm"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
              </span>
            <?php endif; ?>
            <meta itemprop="position" content="<?= $position ?>">
          </li>
          <?php if ($index < $count - 1): ?>
            <li class="breadcrumb__separator flex items-center">
              <i class="fa-solid fa-chevron-right text-xs"></i>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ol>
    <?php else: ?>
      <?php foreach ($items as $index => $item): ?>
        <?php if (isset($item['url']) && isset($item['active']) && !$item['active']): ?>
          <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>" class="breadcrumb__link flex items-center gap-1">
            <?php if ($index === 0): ?>
              <div class="breadcrumb__icon-wrapper flex items-center justify-center">
                <i class="fa-regular fa-house"></i>
              </div>
            <?php endif; ?>
            <span class="breadcrumb__text text-sm"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
          </a>
        <?php else: ?>
          <div class="breadcrumb__link breadcrumb__link--active flex items-center font-medium">
            <span class="breadcrumb__text text-sm"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        <?php endif; ?>
        <?php if ($index < $count - 1): ?>
          <i class="fa-solid fa-chevron-right text-xs"></i>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</nav>