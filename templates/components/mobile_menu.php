<?php

use App\Models\MenuItem;

function renderMobileMenuItem(MenuItem $item, int $depth = 0): void
{
  $hasChildren = $item->hasChildren();
  $itemUrl = htmlspecialchars(url($item->url ?: '#'));
  $itemLabel = htmlspecialchars($item->label);
  $itemId = 'mobile-menu-item-' . (int) $item->id . '-' . $depth;
  ?>
  <li class="mobile-menu__item" data-depth="<?= $depth ?>">
    <div class="mobile-menu__item-row">
      <a class="mobile-menu__link navbar__link" href="<?= $itemUrl ?>">
        <?= $itemLabel ?>
      </a>
      <?php if ($hasChildren): ?>
        <button class="mobile-menu__submenu-toggle" type="button" data-mobile-submenu-toggle
          aria-controls="<?= $itemId ?>" aria-expanded="false" aria-label="Mở menu <?= $itemLabel ?>">
          <i class="fa-solid fa-chevron-down"></i>
        </button>
      <?php endif; ?>
    </div>

    <?php if ($hasChildren): ?>
      <ul class="mobile-menu__list mobile-menu__sublist" id="<?= $itemId ?>" hidden>
        <?php foreach ($item->children as $child): ?>
          <?php renderMobileMenuItem($child, $depth + 1); ?>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </li>
  <?php
}

function renderMobileMenu(array $menu): void
{
  ?>
  <nav class="mobile-menu" id="site-mobile-menu" data-mobile-menu hidden aria-label="Menu di động">
    <div class="container px-4 py-3">
      <ul class="mobile-menu__list">
        <?php foreach ($menu as $item): ?>
          <?php renderMobileMenuItem($item); ?>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>
  <?php
}
