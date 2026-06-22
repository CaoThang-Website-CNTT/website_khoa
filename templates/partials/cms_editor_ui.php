<?php $layout->start('topbar_left') ?>
<a href="<?= url('admin/cms-pages') ?>" class="btn" data-size="md" data-variant="outline">
  <i class="fa-solid fa-chevron-left"></i> Back
</a>
<button type="button" class="btn" data-size="md" data-variant="outline" id="be-toggle-left">
  <i class="fa-solid fa-cube"></i> Blocks
</button>
<?php $layout->end() ?>

<?php $layout->start('topbar_center') ?>
<input id="cms-page-title-input" class="field__input be-post-title-input" type="text" value="<?= htmlspecialchars($page->title) ?>" autocomplete="off">
<span class="badge" data-variant="<?= $page->status === 'published' ? 'primary' : 'secondary' ?>" id="cms-status-badge">
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
<button type="button" class="btn" data-size="md" data-variant="outline" id="be-toggle-right">
  <i class="fa-solid fa-table-columns"></i> Page settings
</button>
<button form="cms-page-form" type="submit" class="btn" data-size="md" data-variant="outline" name="action" value="draft">
  Save draft
</button>
<button form="cms-page-form" type="submit" class="btn" data-size="md" data-variant="primary" name="action" value="publish">
  Publish page
</button>
<?php $layout->end() ?>

<?php $layout->start('left_panel') ?>
<div id="be-left" class="be-panel" data-tabs data-tabs-id="cms-left-panel" data-tabs-panel-active="cms-blocks-panel" data-tabs-sync="false">
  <div class="tabs__list be-panel__tabs-list" role="tablist">
    <button type="button" class="be-panel__tabs-trigger active" data-tabs-trigger="cms-blocks-panel">Blocks</button>
    <button type="button" class="be-panel__tabs-trigger" data-tabs-trigger="cms-structure-panel">Structure</button>
  </div>
  <div class="be-panel__content">
    <div id="cms-block-library" class="tabs__panel" data-tabs-panel="cms-blocks-panel" role="tabpanel"></div>
    <div id="cms-structure-list" class="tabs__panel" data-tabs-panel="cms-structure-panel" role="tabpanel"></div>
  </div>
</div>
<?php $layout->end() ?>

<?php $layout->start('canvas') ?>
<div id="cms-editor-error" class="cms-editor-error"></div>
<div id="cms-builder-preview" class="cms-builder-preview">
  <div id="cms-builder-page" class="cms-builder-page">
    <div id="cms-builder-block-list" class="cms-builder-block-list"></div>
  </div>
</div>
<?php $layout->end() ?>

<?php $layout->start('right_panel') ?>
<div id="be-right" class="be-panel" data-tabs data-tabs-id="cms-right-panel" data-tabs-panel-active="cms-block-settings-panel" data-tabs-sync="false">
  <div class="tabs__list be-panel__tabs-list" role="tablist">
    <button type="button" class="be-panel__tabs-trigger active" data-tabs-trigger="cms-block-settings-panel">Block</button>
    <button type="button" class="be-panel__tabs-trigger" data-tabs-trigger="cms-page-settings-panel">Page</button>
  </div>
  <div class="be-panel__content">
    <div id="cms-block-settings-panel" class="tabs__panel" data-tabs-panel="cms-block-settings-panel" role="tabpanel"></div>
    <div id="cms-page-settings-panel" class="tabs__panel" data-tabs-panel="cms-page-settings-panel" role="tabpanel">
      <div class="field-group">
        <div class="field">
          <span class="field__label">Page</span>
          <div class="cms-field-path"><?= htmlspecialchars($schema['route_path']) ?></div>
        </div>
        <div class="field">
          <span class="field__label">Slug</span>
          <div class="cms-field-path"><?= htmlspecialchars($schema['slug']) ?></div>
        </div>
        <div class="field">
          <span class="field__label">Layout mode</span>
          <div class="cms-field-path"><?= htmlspecialchars($schema['layout_mode']) ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $layout->end() ?>

<?php $layout->start('content') ?>
<form id="cms-page-form" method="POST" action="<?= url('admin/cms-pages/' . $schema['slug']) ?>" class="hidden">
  <?= csrf_field() ?>
  <input type="hidden" id="cms-editor-data" name="editor_data">
</form>
<?php $layout->end() ?>
