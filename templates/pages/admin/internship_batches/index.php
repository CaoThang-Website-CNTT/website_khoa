<?php

use App\Enums\BatchStatus;
use App\Models\InternshipBatch;

?>
<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Đợt Thực Tập
  <span class="badge" data-variant="primary">
    <?= $data->getTotal() ?>
  </span>
  </h2>
  <p class="title-wrapper__description">Quản lý các đợt thực tập của khoa</p>
  <?php $layout->end() ?>

  <?php $layout->start('actions') ?>
  <a href="<?= url('admin/internship_batches/create') ?>" data-variant="primary" data-size="md" class="btn">
    <i class="fa-solid fa-plus"></i>
    Thêm đợt mới
  </a>
  <?php $layout->end() ?>
  <div class="tm-container" data-tm="batches_table" data-tm-mode="client" data-tm-searchable>

    <!-- Cột ID -->
    <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

    <!-- Cột Tên đợt -->
    <template data-tm-col="title" data-tm-label="Tên đợt" data-tm-sortable data-tm-filter-type="text">
      <a href="<?= url('admin/internship_batches/') ?>{{ row.id }}" class="font-medium">{{ value }}</a>
    </template>

    <!-- Cột Ngày bắt đầu -->
    <template data-tm-col="start_at" data-tm-label="Bắt đầu" data-tm-sortable></template>

    <!-- Cột Ngày kết thúc -->
    <template data-tm-col="end_at" data-tm-label="Kết thúc" data-tm-sortable></template>

    <!-- Cột Trạng thái -->
    <template data-tm-col="effective_status" data-tm-label="Trạng thái" data-tm-filter-type="select"
      data-tm-filter-options='<?= json_encode(BatchStatus::getEffectiveOptions()) ?>'>
      <span class="badge" data-variant="{{ row.effective_status_variant }}">
        {{ row.effective_status_label }}
      </span>
    </template>

    <template data-tm-pagination></template>
  </div>

  <!-- JSON Data Source cho TableManager -->

  <?php $layout->start("scripts") ?>
  <script type="application/json" data-tm-data="batches_table">
  <?= json_encode([
    'rows' => array_map(function ($batch) {
        $b = (object) $batch;

        $batchModel = new InternshipBatch();
        $batchModel->status = $b->status ?? 'draft';
        $batchModel->start_at = $b->start_at ?? null;
        $batchModel->end_at = $b->end_at ?? null;

        $effStatus = $batchModel->getEffectiveStatus();

        return [
          'id' => $b->id,
          'title' => $b->title ?? 'N/A',
          'class_of' => $b->class_of ?? 'N/A',
          'level' => $b->level ?? 'N/A',
          'start_at' => $b->start_at ? date('d/m/Y', strtotime($b->start_at)) : 'N/A',
          'end_at' => $b->end_at ? date('d/m/Y', strtotime($b->end_at)) : 'N/A',
          'status' => $b->status ?? 'draft',
          'effective_status' => $effStatus,
          'effective_status_label' => BatchStatus::getLabel($effStatus),
          'effective_status_variant' => BatchStatus::getVariant($effStatus)
        ];
      }, $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>
  <?php $layout->end() ?>
