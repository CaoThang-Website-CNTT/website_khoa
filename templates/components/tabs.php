<?php
// Yêu cầu:
// $tabs: Mảng chứa thông tin các tab
// $tabPanels: Mảng chứa nội dung của các tab
// $activeTab: Tab mặc định khi load trang
// $tabsId: ID của tab

// data attributes:
// data-tabs: Đánh dấu là tabs component.
// data-id: ID của tabs (Phân biệt các tabs trong cùng 1 trang).
// data-active: tab đang active.
// data-tabs-trigger: Nút chuyển tab.
// data-tabs-panel: tab panel chứa nội dung.

if (!isset($tabs, $tabPanels, $tabsId)) {
  throw new \RuntimeException('tabs.php requires $tabs, $tabPanels, and $tabsId to be set.');
}

$activeTab = $activeTab ?? $tabs[0]['key'];
?>

<div class="tabs" data-tabs data-id="<?= htmlspecialchars($tabsId) ?>"
  data-active="<?= htmlspecialchars($activeTab) ?>">

  <div class="tabs__list" role="tablist">
    <?php foreach ($tabs as $tab): ?>
      <a href="#<?= htmlspecialchars($tabsId) ?>:<?= htmlspecialchars($tab['key']) ?>" role="tab" aria-selected="false"
        aria-controls="<?= htmlspecialchars($tabsId) ?>-panel-<?= htmlspecialchars($tab['key']) ?>"
        data-tabs-trigger="<?= htmlspecialchars($tab['key']) ?>" class="tabs__trigger">
        <?= htmlspecialchars($tab['label']) ?>
        <?php if (!empty($tab['badge'])): ?>
          <span class="tabs__badge badge" data-variant="default">
            <?= (int) $tab['badge'] ?>
          </span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php foreach ($tabs as $tab): ?>
    <div id="<?= htmlspecialchars($tabsId) ?>-panel-<?= htmlspecialchars($tab['key']) ?>" role="tabpanel"
      data-tabs-panel="<?= htmlspecialchars($tab['key']) ?>" class="tabs__panel">
      <?= $tabPanels[$tab['key']] ?? '' ?>
    </div>
  <?php endforeach; ?>

</div>