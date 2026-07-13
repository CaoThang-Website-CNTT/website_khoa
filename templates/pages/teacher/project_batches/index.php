<?php

use App\Enums\ProjectBatchStatus;
use App\Models\ProjectBatch;
?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Đợt Đồ Án Tốt Nghiệp
  <span class="badge" data-variant="primary">
    <?= $data->getTotal() ?>
  </span>
</h2>
<p class="title-wrapper__description">Quản lý các đợt đồ án tốt nghiệp mà giảng viên tham gia hướng dẫn</p>
<?php $layout->end() ?>

<div class="tm-container" data-tm="batches_table" data-tm-mode="client" data-tm-searchable>

  <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

  <template data-tm-col="title" data-tm-label="Tên đợt" data-tm-sortable data-tm-filter-type="text">
    <a href="<?= url('teacher/project_batches/') ?>{{ row.id }}" class="font-medium">{{ value }}</a>
  </template>

  <template data-tm-col="topic_proposal_start" data-tm-label="Bắt đầu đề xuất" data-tm-sortable></template>
  <template data-tm-col="topic_proposal_end" data-tm-label="Kết thúc đề xuất" data-tm-sortable></template>

  <template data-tm-col="effective_status" data-tm-label="Trạng thái" data-tm-filter-type="select"
    data-tm-filter-options='<?= json_encode(ProjectBatchStatus::getEffectiveOptions()) ?>'>
    <span class="badge" data-variant="{{ row.effective_status_variant }}">
      {{ row.effective_status_label }}
    </span>
  </template>

  <template data-tm-pagination></template>
</div>

<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="batches_table">
  <?= json_encode([
    'rows' => array_map(function ($batch) {
      $b = (object) $batch;
      return [
        'id' => $b->id,
        'title' => $b->title ?? 'N/A',
        'topic_proposal_start' => $b->topic_proposal_start ? date('d/m/Y', strtotime($b->topic_proposal_start)) : 'N/A',
        'topic_proposal_end' => $b->topic_proposal_end ? date('d/m/Y', strtotime($b->topic_proposal_end)) : 'N/A',
        'effective_status' => (function () use ($b) {
          $batchModel = new ProjectBatch();
          $batchModel->status = $b->status ?? 'draft';
          $batchModel->topic_proposal_start = $b->topic_proposal_start ?? null;
          $batchModel->topic_proposal_end = $b->topic_proposal_end ?? null;
          $batchModel->registration_start = $b->registration_start ?? null;
          $batchModel->registration_end = $b->registration_end ?? null;
          $batchModel->allocation_published_at = $b->allocation_published_at ?? null;
          return $batchModel->getEffectivePhase();
        })(),
        'effective_status_label' => (function () use ($b) {
          $batchModel = new ProjectBatch();
          $batchModel->status = $b->status ?? 'draft';
          $batchModel->topic_proposal_start = $b->topic_proposal_start ?? null;
          $batchModel->topic_proposal_end = $b->topic_proposal_end ?? null;
          $batchModel->registration_start = $b->registration_start ?? null;
          $batchModel->registration_end = $b->registration_end ?? null;
          $batchModel->allocation_published_at = $b->allocation_published_at ?? null;
          return ProjectBatchStatus::getLabel($batchModel->getEffectivePhase());
        })(),
        'effective_status_variant' => (function () use ($b) {
          $batchModel = new ProjectBatch();
          $batchModel->status = $b->status ?? 'draft';
          $batchModel->topic_proposal_start = $b->topic_proposal_start ?? null;
          $batchModel->topic_proposal_end = $b->topic_proposal_end ?? null;
          $batchModel->registration_start = $b->registration_start ?? null;
          $batchModel->registration_end = $b->registration_end ?? null;
          $batchModel->allocation_published_at = $b->allocation_published_at ?? null;
          return ProjectBatchStatus::getVariant($batchModel->getEffectivePhase());
        })(),
      ];
    }, $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>
<script>
  (() => {
    const root = document.querySelector('[data-tm="batches_table"]');
    if (!root) return;

    const enhanceRows = () => {
      root.querySelectorAll('.tm-tbody .tm-tr').forEach((row) => {
        if (row.dataset.rowNavigationReady) return;
        const link = row.querySelector('a[href]');
        if (!link) return;

        row.dataset.rowNavigationReady = 'true';
        row.classList.add('tm-tr--interactive');
        row.tabIndex = 0;
        row.setAttribute('role', 'link');
        row.setAttribute('aria-label', `Xem chi tiết ${link.textContent.trim()}`);
        row.addEventListener('click', (event) => {
          if (event.target.closest('a, button, input, select, textarea')) return;
          window.location.href = link.href;
        });
        row.addEventListener('keydown', (event) => {
          if (event.target !== row || !['Enter', ' '].includes(event.key)) return;
          event.preventDefault();
          window.location.href = link.href;
        });
      });
    };

    root.addEventListener('tm:render', enhanceRows);
    document.addEventListener('DOMContentLoaded', enhanceRows);
  })();
</script>
<?php $layout->end() ?>
