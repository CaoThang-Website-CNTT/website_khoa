<?php

use App\Models\MenuItem;

/**
 * Render một leaf item (.dropdown__item) — dùng cho cả depth 1 và depth 2+.
 * @param MenuItem $item
 */
function renderLeafItem(MenuItem $item): void
{
  $disabled = !empty($item->disabled) ? 'data-disabled' : '';
  $hasUrl = $item->url && $item->url !== '#';
  ?>
  <div class="dropdown__item" role="menuitem" tabindex="-1" <?= $disabled ?>>
    <?php if ($hasUrl): ?>
      <a href="<?= htmlspecialchars($item->url) ?>" class="navbar__link">
        <?= htmlspecialchars($item->label) ?>
      </a>
    <?php else: ?>
      <span><?= htmlspecialchars($item->label) ?></span>
    <?php endif; ?>
  </div>
  <?php
}

/**
 * Render một item và các con của nó theo cấu trúc Dropdown BEM.
 *
 * Depth 0 → .dropdown > .dropdown__trigger + .dropdown__content
 * Depth 1 → .dropdown__item hoặc .dropdown__sub > .dropdown__sub-trigger + .dropdown__sub-content
 * Depth 2 → .dropdown__item (leaf)
 *
 * @param MenuItem $item
 * @param string   $triggerMode  "hover" | "click"
 * @param int      $depth
 * @param int      $maxDepth
 */
function renderDropdownItem(
  MenuItem $item,
  string $triggerMode = 'hover',
  int $depth = 0,
  int $maxDepth = 2
): void {
  if ($depth > $maxDepth)
    return;

  $hasChildren = $item->hasChildren();

  /* ---- Depth 0: Root trigger + content panel --------------------- */
  if ($depth === 0) {
    $activeClass = $item->isActive(request()->path()) ? ' navbar__item--active' : '';
    ?>
    <div class="dropdown">

      <div class="navbar__item<?= $activeClass ?> dropdown__trigger"
        data-dropdown-trigger-mode="<?= htmlspecialchars($triggerMode) ?>" data-state="closed" role="button" tabindex="0"
        aria-haspopup="menu" aria-expanded="false">
        <a href="<?= htmlspecialchars($item->url) ?>" class="navbar__link" tabindex="-1">
          <?= htmlspecialchars($item->label) ?>
        </a>
        <?php if ($hasChildren): ?>
          <svg class="dropdown__chevron" viewBox="0 0 16 16" fill="none" aria-hidden="true" width="16" height="16">
            <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        <?php endif; ?>
      </div>

      <?php if ($hasChildren): ?>
        <div class="navbar__item-content dropdown__content" data-state="closed" role="menu">
          <?php foreach ($item->children as $child): ?>
            <?php renderDropdownItem($child, $triggerMode, 1, $maxDepth); ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
    <?php

    /* ---- Depth 1: Sub hoặc leaf item bên trong content ------------- */
  } elseif ($depth === 1) {

    if ($hasChildren) {
      $disabled = !empty($item->disabled) ? 'data-disabled' : '';
      ?>
      <div class="dropdown__sub">

        <div class="dropdown__sub-trigger dropdown__item" data-state="closed" role="menuitem" aria-haspopup="menu"
          aria-expanded="false" <?= $disabled ?>>
          <a href="<?= htmlspecialchars($item->url) ?>" class="navbar__link" tabindex="-1">
            <?= htmlspecialchars($item->label) ?>
            <svg class="dropdown__item-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true" width="14" height="14">
              <path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
          </a>
        </div>

        <div class="navbar__item-content dropdown__sub-content" data-state="closed" role="menu">
          <?php foreach ($item->children as $child): ?>
            <?php renderDropdownItem($child, $triggerMode, 2, $maxDepth); ?>
          <?php endforeach; ?>
        </div>

      </div>
      <?php
    } else {
      renderLeafItem($item);
    }

    /* ---- Depth 2+: Leaf item bên trong SubContent ----------------- */
  } else {
    renderLeafItem($item);
  }
}

/**
 * Render thanh navbar với danh sách item gốc.
 * Items vượt quá $maxVisible được gộp vào nhóm "Khác".
 *
 * Usage:
 *   renderNav($menu);                    // hover, 5 visible, 2 levels
 *   renderNav($menu, 7);                 // show 7 trước "Khác"
 *   renderNav($menu, 5, 'click');        // click mode
 *   renderNav($menu, 5, 'hover', 3);     // 3 levels deep
 *
 * @param MenuItem[] $menu
 * @param int        $maxVisible
 * @param string     $triggerMode  "hover" | "click"
 * @param int        $maxDepth
 */
function renderNav(
  array $menu,
  int $maxVisible = 5,
  string $triggerMode = 'hover',
  int $maxDepth = 2
): void {
  $navItems = $menu;

  if (count($menu) > $maxVisible) {
    $khac = new MenuItem(
      id: 0,
      menu_id: $menu[0]->menu_id,
      parent_id: null,
      label: 'Khác',
      url: '#',
      sort_order: $maxVisible + 1,
      created_at: '',
      updated_at: '',
      deleted_at: null,
    );
    $khac->children = array_slice($menu, $maxVisible);
    $navItems = [...array_slice($menu, 0, $maxVisible), $khac];
  }
  ?>
  <nav class="navbar">
    <div class="container flex py-2 px-4 gap-4">
      <?php foreach ($navItems as $item): ?>
        <?php if ($item->hasChildren()): ?>
          <?php renderDropdownItem($item, $triggerMode, 0, $maxDepth); ?>
        <?php else: ?>
          <div class="navbar__item <?= $item->isActive(request()->path()) ? 'navbar__item--active' : '' ?>">
            <a href="<?= htmlspecialchars($item->url) ?>" class="navbar__link">
              <?= htmlspecialchars($item->label) ?>
            </a>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </nav>
  <?php
}