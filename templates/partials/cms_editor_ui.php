<?php $layout->start('topbar_left') ?>
<a href="<?= url('admin/cms-pages') ?>" class="btn" data-size="md" data-variant="outline">
  <i class="fa-solid fa-chevron-left"></i> Quay lại
</a>
<button type="button" class="btn" data-size="md" data-variant="outline" id="be-toggle-left">
  <i class="fa-solid fa-layer-group"></i> Sections
</button>
<?php $layout->end() ?>

<?php $layout->start('topbar_center') ?>
<div class="field" data-field-readonly>
  <label class="field__label sr-only" for="cms-page-title">Tiêu đề trang</label>
  <input id="cms-page-title" class="field__input be-post-title-input" type="text"
    value="<?= htmlspecialchars($page->title) ?>" readonly>
</div>
<span class="badge" data-variant="<?= $page->status === 'published' ? 'primary' : 'secondary' ?>">
  <?= htmlspecialchars($page->status) ?>
</span>
<?php $layout->end() ?>

<?php $layout->start('topbar_right') ?>
<button type="button" class="btn" data-size="md" data-variant="outline" data-preview-width="mobile">
  <i class="fa-solid fa-mobile-screen"></i>
</button>
<button type="button" class="btn" data-size="md" data-variant="outline" data-preview-width="desktop">
  <i class="fa-solid fa-desktop"></i>
</button>
<button type="button" class="btn" data-size="md" data-variant="outline" data-preview-highlight>
  <i class="fa-solid fa-highlighter"></i>
</button>
<button type="button" class="btn" data-size="md" data-variant="outline" id="be-toggle-right">
  <i class="fa-solid fa-table-columns"></i> Fields
</button>
<button form="cms-page-form" type="submit" class="btn" data-size="md" data-variant="primary" name="action"
  value="draft">
  Luu
</button>
<?php $layout->end() ?>

<?php $layout->start('left_panel') ?>
<div id="be-left" class="be-panel">
  <div class="tabs__list be-panel__tabs-list">
    <button type="button" class="be-panel__tabs-trigger active">Sections</button>
  </div>
  <div class="be-panel__content">
    <div id="cms-section-list" class="cms-section-list"></div>
  </div>
</div>
<?php $layout->end() ?>

<?php $layout->start('canvas') ?>
<div id="cms-editor-error" class="cms-editor-error"></div>
<div id="cms-preview" class="cms-preview"></div>
<?php $layout->end() ?>

<?php $layout->start('right_panel') ?>
<div id="be-right" class="be-panel">
  <div class="tabs__list be-panel__tabs-list">
    <button type="button" class="be-panel__tabs-trigger active">Section Fields</button>
  </div>
  <div class="be-panel__content" id="cms-section-inspector"></div>
</div>
<?php $layout->end() ?>

<?php $layout->start('content') ?>
<form id="cms-page-form" method="POST" action="<?= url('admin/cms-pages/' . $schema['slug']) ?>" class="hidden">
  <?= csrf_field() ?>
  <input type="hidden" id="cms-editor-data" name="editor_data">
</form>
<?php $layout->end() ?>
