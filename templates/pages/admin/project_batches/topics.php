<?php

use App\Enums\ProjectTopicStatus;

$batchObj = (object) $batch;
$statuses = array_merge(['all' => ['label' => 'Tất cả', 'variant' => 'secondary']], ProjectTopicStatus::getMetadata());
?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Quản lý đề tài</h2>
<p class="title-wrapper__description">Xét duyệt đề tài trong đợt <?= htmlspecialchars($batchObj->title) ?></p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("admin/project_batches/{$batchObj->id}") ?>" class="btn" data-variant="outline" data-size="lg">
  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
  Quay lại
</a>
<?php $layout->end() ?>

<?php $layout->start('content') ?>
<div id="project-topics-page" aria-busy="true">
  <div class="tabs mb-4" data-topic-tabs>
    <div class="tabs__list" role="tablist" aria-label="Lọc theo trạng thái đề tài">
      <?php foreach ($statuses as $value => $metadata): ?>
        <button type="button" class="tabs__trigger" role="tab"
          data-topic-status="<?= htmlspecialchars($value) ?>"
          data-tabs-trigger-state="idle" aria-selected="false" tabindex="-1">
          <?= htmlspecialchars($metadata['label']) ?>
          <span class="badge" data-topic-count="<?= htmlspecialchars($value) ?>" data-variant="primary">0</span>
        </button>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="alert mb-4 hidden" data-topic-error data-variant="destructive" role="alert">
    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
    <div>
      <div class="alert-title">Không thể tải danh sách đề tài</div>
      <div class="alert-description" data-topic-error-message></div>
      <button type="button" class="btn mt-2" data-variant="outline" data-size="sm" data-topic-retry>Thử lại</button>
    </div>
  </div>

  <div class="tm-container" id="topics_table" data-tm="topics_table" data-tm-mode="server"
    data-tm-searchable data-tm-selectable="true" data-tm-id-key="id">
    <template data-tm-col="title" data-tm-label="Đề tài">
      <div class="font-medium">{{ value }}</div>
      <div class="text-sm mt-1" style="white-space: pre-line; color: var(--muted-foreground);">{{ row.description }}</div>
    </template>
    <template data-tm-col="teacher" data-tm-label="Giảng viên">
      <div class="font-medium">{{ value.name }}</div>
      <div class="text-sm" style="color: var(--muted-foreground);">{{ value.department }}</div>
    </template>
    <template data-tm-col="max_students" data-tm-label="Số SV tối đa"></template>
    <template data-tm-col="status" data-tm-label="Trạng thái">
      <span class="badge" data-variant="{{ value.variant }}">{{ value.label }}</span>
    </template>
    <template data-tm-col="_actions" data-tm-label="Hành động" data-tm-width="176px">
      <div class="flex flex-nowrap gap-1" data-id="{{ row.id }}" style="white-space: nowrap;">
        <a href="{{ row.pdf_file_url || '#' }}" class="btn {{ !row.pdf_file_url ? 'hidden' : '' }}" data-size="sm" data-variant="outline" target="_blank" rel="noopener" aria-label="Xem tệp mô tả">
          <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
        </a>
        <button type="button" class="btn btn-approve {{ row.status.value !== 'pending' ? 'hidden' : '' }}" data-size="sm" data-variant="primary" data-id="{{ row.id }}">Duyệt</button>
        <button type="button" class="btn btn-reject {{ row.status.value !== 'pending' ? 'hidden' : '' }}" data-size="sm" data-variant="destructive" data-id="{{ row.id }}">Từ chối</button>
        <button type="button" class="btn btn-reason {{ row.status.value !== 'rejected' || !row.reject_reason ? 'hidden' : '' }}" data-size="sm" data-variant="outline" data-reason="{{ row.reject_reason }}">Lý do</button>
      </div>
    </template>
    <template data-tm-pagination></template>
  </div>
</div>

<div class="modal" id="approve-topic-modal" tabindex="-1" data-state="closed" role="dialog" aria-modal="true" aria-labelledby="approve-topic-title">
  <div class="modal__header">
    <h3 class="modal__title" id="approve-topic-title">Duyệt đề tài</h3>
    <p class="modal__description">Đề tài đã duyệt sẽ sẵn sàng để công bố cho sinh viên đăng ký.</p>
  </div>
  <div class="modal__footer">
    <button type="button" class="btn" data-modal-close data-variant="outline" data-size="lg">Hủy</button>
    <button type="button" class="btn" id="confirm-approve-topic" data-variant="primary" data-size="lg">Duyệt đề tài</button>
  </div>
  <button type="button" class="modal__close" data-modal-close aria-label="Đóng"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
</div>

<div class="modal" id="reject-topic-modal" tabindex="-1" data-state="closed" role="dialog" aria-modal="true" aria-labelledby="reject-topic-title">
  <div class="modal__header">
    <h3 class="modal__title" id="reject-topic-title">Từ chối đề tài</h3>
    <p class="modal__description">Giảng viên sẽ nhận được lý do này để chỉnh sửa đề tài.</p>
  </div>
  <div class="modal__content">
    <div class="field" data-field-required>
      <label class="field__label" for="reject-topic-reason">Lý do từ chối</label>
      <textarea id="reject-topic-reason" class="field__input" rows="4" maxlength="1000" required></textarea>
      <p class="field__error hidden" id="reject-topic-error">Vui lòng nhập lý do từ chối.</p>
    </div>
  </div>
  <div class="modal__footer">
    <button type="button" class="btn" data-modal-close data-variant="outline" data-size="lg">Hủy</button>
    <button type="button" class="btn" id="confirm-reject-topic" data-variant="destructive" data-size="lg">Từ chối</button>
  </div>
  <button type="button" class="modal__close" data-modal-close aria-label="Đóng"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
</div>

<div class="modal" id="topic-reason-modal" tabindex="-1" data-state="closed" role="dialog" aria-modal="true" aria-labelledby="topic-reason-title">
  <div class="modal__header"><h3 class="modal__title" id="topic-reason-title">Lý do từ chối</h3></div>
  <div class="modal__content"><p data-topic-reason-text style="white-space: pre-line;"></p></div>
  <div class="modal__footer"><button type="button" class="btn" data-modal-close data-variant="outline" data-size="lg">Đóng</button></div>
  <button type="button" class="modal__close" data-modal-close aria-label="Đóng"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
</div>

<script type="application/json" id="project-topics-config"><?= json_encode([
  'batchId' => (int) $batchObj->id,
  'listUrl' => url("api/v1/project_batches/{$batchObj->id}/topics"),
  'topicApiUrl' => url('api/v1/project_topics'),
  'csrfToken' => csrf_token(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php $layout->end() ?>

<?php $layout->start('scripts') ?>
<script type="module" src="<?= url('public/js/pages/admin/project_topics.js') ?>"></script>
<?php $layout->end() ?>
