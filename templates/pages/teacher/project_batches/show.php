<?php

use App\Enums\ProjectBatchStatus;
use App\Enums\ProjectTopicStatus;
use App\Models\ProjectBatch;
?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Chi tiết đợt: <?= htmlspecialchars($batch['title']) ?>
</h2>
<p class="title-wrapper__description">Danh sách đề tài do bạn đề xuất trong đợt này</p>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('teacher/project_batches') ?>" data-variant="outline" data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<?php
$canPropose = false;
if (!empty($batch['topic_proposal_start']) && !empty($batch['topic_proposal_end'])) {
  $now = new \DateTime();
  $start = new \DateTime($batch['topic_proposal_start']);
  $end = new \DateTime($batch['topic_proposal_end']);
  if ($now >= $start && $now <= $end) {
    $canPropose = true;
  }
}

if ($canPropose):
?>
  <a href="<?= url("teacher/project_batches/{$batch['id']}/topics/create") ?>" class="btn" data-variant="primary" data-size="lg">
    <i class="fa-solid fa-plus"></i> Gửi đề tài
  </a>
<?php endif; ?>
<?php $layout->end() ?>

<?php $layout->start('content') ?>
<div class="card mb-6">
  <div class="card__header">
    <legend class="card__title field__legend">Thông tin đợt đồ án</legend>
  </div>
  <hr class="separator">
  <div class="card__content">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <p class="text-sm">Niên khóa</p>
        <p class="font-medium"><?= htmlspecialchars($batch['class_of']) ?></p>
      </div>
      <div>
        <p class="text-sm">Trạng thái</p>
        <?php
        $batchModel = new ProjectBatch();
        $batchModel->status = $batch['status'] ?? 'draft';
        $batchModel->topic_proposal_start = $batch['topic_proposal_start'] ?? null;
        $batchModel->topic_proposal_end = $batch['topic_proposal_end'] ?? null;
        $batchModel->registration_start = $batch['registration_start'] ?? null;
        $batchModel->registration_end = $batch['registration_end'] ?? null;
        $effectiveStatus = $batchModel->getEffectivePhase();
        ?>
        <span class="badge" data-variant="<?= ProjectBatchStatus::getVariant($effectiveStatus) ?>">
          <?= ProjectBatchStatus::getLabel($effectiveStatus) ?>
        </span>
      </div>
      <div>
        <p class="text-sm">Thời gian đề xuất đề tài</p>
        <p class="font-medium">
          <?= !empty($batch['topic_proposal_start']) ? date('d/m/Y', strtotime($batch['topic_proposal_start'])) : 'Chưa thiết lập' ?>
          -
          <?= !empty($batch['topic_proposal_end']) ? date('d/m/Y', strtotime($batch['topic_proposal_end'])) : 'Chưa thiết lập' ?>
        </p>
      </div>
      <div>
        <p class="text-sm">Thời gian đăng ký đề tài</p>
        <p class="font-medium">
          <?= !empty($batch['registration_start']) ? date('d/m/Y', strtotime($batch['registration_start'])) : 'Chưa thiết lập' ?>
          -
          <?= !empty($batch['registration_end']) ? date('d/m/Y', strtotime($batch['registration_end'])) : 'Chưa thiết lập' ?>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card__header">
    <h3 class="font-semibold">Danh sách đề tài</h3>
  </div>

  <hr class="separator">
  <div class="card__content">
    <div class="tm-container" data-tm="topics_table" data-tm-mode="client" data-tm-searchable>

      <template data-tm-col="title" data-tm-label="Tên đề tài" data-tm-sortable data-tm-filter-type="text">
        <span class="font-medium">{{ value }}</span>
      </template>

      <template data-tm-col="max_students" data-tm-label="Số lượng sinh viên" data-tm-width="80px"></template>

      <template data-tm-col="status" data-tm-label="Trạng thái" data-tm-filter-type="select"
        data-tm-filter-options='<?= json_encode(ProjectTopicStatus::getOptions()) ?>'>
        <span class="badge" data-variant="{{ row.status_variant }}">
          {{ row.status_label }}
        </span>
      </template>

      <template data-tm-col="actions" data-tm-label="" data-tm-width="120px">
        <div class="flex gap-2 justify-end">
          {{#if row.can_edit}}
            <a href="{{ row.edit_url }}" class="btn btn--icon" data-size="md" data-variant="outline" aria-label="Sửa" title="Sửa">
              <i class="fa-solid fa-pencil"></i>
            </a>
            <form action="{{ row.delete_url }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đề tài này không?');">
              <button type="submit" class="btn btn--icon" data-variant="destructive" data-size="md" aria-label="Xóa" title="Xóa">
                <i class="fa-solid fa-trash"></i>
              </button>
            </form>
          {{/if}}

          {{#if row.pdf_url}}
            <a href="{{ row.pdf_url }}" target="_blank" class="btn btn--icon" data-variant="outline" data-size="md" aria-label="Xem PDF" title="Xem PDF">
              <i class="fa-solid fa-file-pdf"></i>
            </a>
          {{/if}}
        </div>
      </template>

      <template data-tm-pagination></template>
    </div>
  </div>
</div>

<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="topics_table">
  <?= json_encode([
    'rows' => array_map(function ($topic) use ($batch) {
      $t = (object) $topic;
      return [
        'id' => $t->id,
        'title' => $t->title,
        'max_students' => $t->max_students,
        'status' => $t->status,
        'status_label' => ProjectTopicStatus::getLabel($t->status),
        'status_variant' => ProjectTopicStatus::getVariant($t->status),
        'can_edit' => in_array($t->status, [ProjectTopicStatus::DRAFT, ProjectTopicStatus::REJECTED]),
        'edit_url' => url("teacher/project_batches/{$batch['id']}/topics/{$t->id}/edit"),
        'delete_url' => url("teacher/project_batches/{$batch['id']}/topics/{$t->id}/delete"),
        'pdf_url' => $t->pdf_file_path ? url("storage/" . $t->pdf_file_path) : null
      ];
    }, $topics),
    'total' => count($topics),
    'page' => 1,
    'limit' => count($topics) > 0 ? count($topics) : 15
  ]) ?>
</script>
<?php $layout->end() ?>