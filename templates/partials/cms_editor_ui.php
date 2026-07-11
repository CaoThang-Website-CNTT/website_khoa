<?php $layout->start('topbar_left') ?>
<a href="<?= url('admin/cms-pages') ?>" class="btn" data-size="md" data-variant="outline">
  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Quay lại
</a>
<span class="tooltip" data-side="bottom" data-tooltip-gap="10">
  <button type="button" class="btn tooltip__trigger" data-size="md" data-variant="outline" id="be-toggle-left"
    aria-label="Sections">
    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
  </button>
  <span class="tooltip__content">Sections</span>
</span>
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
<?php include BASE_PATH . '/templates/components/editor_help_trigger.php'; ?>
<span class="tooltip" data-side="bottom" data-tooltip-gap="10">
  <span class="dropdown">
    <button type="button" class="btn tooltip__trigger dropdown__trigger" data-size="md" data-variant="primary"
      data-dropdown-trigger-mode="click" data-preview-viewport-trigger aria-label="Chế độ xem">
      <i class="fa-solid fa-desktop" data-preview-viewport-icon aria-hidden="true"></i>
    </button>
    <span class="dropdown__content" data-state="closed" role="menu">
      <button type="button" class="dropdown__item" data-preview-width="desktop">
        <i class="fa-solid fa-desktop" aria-hidden="true"></i>
        <span class="dropdown__item-label">Desktop</span>
      </button>
      <button type="button" class="dropdown__item" data-preview-width="mobile">
        <i class="fa-solid fa-mobile-screen" aria-hidden="true"></i>
        <span class="dropdown__item-label">Mobile</span>
      </button>
    </span>
  </span>
  <span class="tooltip__content">Chế độ xem</span>
</span>
<span class="tooltip" data-side="bottom" data-tooltip-gap="10">
  <button type="button" class="btn tooltip__trigger" data-size="md" data-variant="outline" data-preview-highlight
    aria-label="Đánh dấu vùng sửa">
    <i class="fa-solid fa-highlighter" aria-hidden="true"></i>
  </button>
  <span class="tooltip__content">Đánh dấu vùng sửa</span>
</span>
<span class="tooltip" data-side="bottom" data-tooltip-gap="10">
  <button type="button" class="btn tooltip__trigger" data-size="md" data-variant="outline" id="be-toggle-right"
    aria-label="Fields">
    <i class="fa-solid fa-table-columns" aria-hidden="true"></i>
  </button>
  <span class="tooltip__content">Fields</span>
</span>
<button form="cms-page-form" type="submit" class="btn" data-size="md" data-variant="primary" name="action"
  value="draft">
  Lưu
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
<div class="modal detail-modal cms-ai-suggestion-modal" id="cms-ai-suggestion-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">AI đề xuất</h3>
    <p class="modal__description">Mô tả nội dung bạn muốn cho trường đang chọn.</p>
  </div>
  <div class="modal__content">
    <div class="field">
      <label class="field__label" for="cms-ai-suggestion-idea">Ý tưởng / yêu cầu</label>
      <textarea id="cms-ai-suggestion-idea" class="field__input" rows="5"
        placeholder="Ví dụ: Viết ngắn gọn, trang trọng, giới thiệu ngành học này."></textarea>
      <div class="field__description cms-ai-status" data-cms-ai-status data-state="ready" role="status"
        aria-live="polite">
        <i class="fa-regular fa-lightbulb" data-cms-ai-status-icon aria-hidden="true"></i>
        <span data-cms-ai-status-text>Nhập yêu cầu để bắt đầu.</span>
      </div>
    </div>
    <div class="cms-ai-preview" data-cms-ai-preview hidden>
      <div class="cms-ai-suggestion__label">Bản xem trước</div>
      <div class="cms-ai-suggestion__text" data-cms-ai-preview-text></div>
    </div>
  </div>
  <div class="modal__footer">
    <button type="button" class="btn" data-variant="outline" data-size="lg" data-modal-close>Hủy</button>
    <button type="button" class="btn" data-variant="primary" data-cms-ai-submit>Nhận đề xuất</button>
    <button type="button" class="btn" data-variant="primary" data-size="lg" data-cms-ai-apply hidden>Áp dụng</button>
  </div>
  <button type="button" class="modal__close" data-modal-close aria-label="Đóng"><i class="fa-solid fa-xmark"
      aria-hidden="true"></i></button>
</div>
<?php $layout->end() ?>