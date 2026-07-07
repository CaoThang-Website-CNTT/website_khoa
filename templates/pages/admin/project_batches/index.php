<?php

use App\Enums\ProjectBatchStatus;
use App\Models\ProjectBatch;

?>
<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Đợt Đồ Án Tốt Nghiệp
  <span class="badge" data-variant="primary">
    <?= $data->getTotal() ?>
  </span>
</h2>
<p class="title-wrapper__description">Quản lý các đợt đồ án tốt nghiệp của khoa</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/project_batches/create') ?>" data-variant="primary" data-size="md" class="btn">
  <i class="fa-solid fa-plus"></i>
  Thêm đợt mới
</a>
<?php $layout->end() ?>

<?php $layout->start('content') ?>

<div class="tm-container" data-tm="batches_table" data-tm-mode="client" data-tm-searchable>
  <!-- Cột ID -->
  <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

  <!-- Cột Tên đợt -->
  <template data-tm-col="title" data-tm-label="Tên đợt" data-tm-sortable data-tm-filter-type="text">
    <a href="<?= url('admin/project_batches/') ?>{{ row.id }}" class="font-medium">{{ value }}</a>
  </template>

  <template data-tm-col="class_of" data-tm-label="Khóa" data-tm-sortable></template>

  <!-- Cột Ngày bắt đầu -->
  <template data-tm-col="registration_start" data-tm-label="ĐK Bắt đầu" data-tm-sortable></template>

  <!-- Cột Ngày kết thúc -->
  <!-- <template data-tm-col="registration_end" data-tm-label="ĐK Kết thúc" data-tm-sortable></template> -->

  <!-- Cột Trạng thái -->
  <template data-tm-col="effective_phase" data-tm-label="Trạng thái" data-tm-filter-type="select"
    data-tm-filter-options='<?= json_encode(ProjectBatchStatus::getEffectiveOptions()) ?>'>
    <span class="badge" data-variant="{{ row.effective_phase_variant }}">
      {{ row.effective_phase_label }}
    </span>
  </template>

  <template data-tm-pagination></template>
</div>

<?php $layout->end() ?>

<!-- JSON Data Source cho TableManager -->
<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="batches_table">
  <?= json_encode([
    'rows' => array_map(function ($batch) {
      $b = (object) $batch;

      $batchModel = new ProjectBatch();
      $batchModel->status = $b->status ?? 'draft';
      $batchModel->topic_proposal_start = $b->topic_proposal_start ?? null;
      $batchModel->topic_proposal_end = $b->topic_proposal_end ?? null;
      $batchModel->registration_start = $b->registration_start ?? null;
      $batchModel->registration_end = $b->registration_end ?? null;

      $effPhase = $batchModel->getEffectivePhase();

      return [
        'id' => $b->id,
        'title' => $b->title ?? 'N/A',
        'class_of' => ($b->min_class_of ?? 'N/A') . ' - ' . ($b->max_class_of ?? 'N/A'),
        'registration_start' => $b->registration_start ? date('d/m/Y', strtotime($b->registration_start)) : 'N/A',
        'registration_end' => $b->registration_end ? date('d/m/Y', strtotime($b->registration_end)) : 'N/A',
        'status' => $b->status ?? 'draft',
        'effective_phase' => $effPhase,
        'effective_phase_label' => ProjectBatchStatus::getLabel($effPhase),
        'effective_phase_variant' => ProjectBatchStatus::getVariant($effPhase)
      ];
    }, $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>
<?php $layout->end() ?>