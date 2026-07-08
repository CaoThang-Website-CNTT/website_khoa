<?php $batchObj = (object)$batch; ?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Giảng viên phụ trách</h2>
<p class="title-wrapper__description"><?= htmlspecialchars($batchObj->title) ?></p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("admin/project_batches/{$batchObj->id}") ?>" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Quay lại
</a>
<?php $layout->end() ?>

<div class="card shadow-sm">
  <div class="card__header">
    <h3 class="font-semibold">Danh sách giảng viên</h3>
    <span class="badge" data-variant="secondary"><?= count($teachers) ?> giảng viên</span>
  </div>
  <hr class="separator">
  <div class="card__content">
    <?php if (empty($teachers)): ?>
      <div class="empty">
        <h3 class="empty__title">Chưa có giảng viên phụ trách</h3>
        <p class="empty__description">Đợt đồ án này chưa được phân công giảng viên.</p>
      </div>
    <?php else: ?>
      <div class="tm-container" data-tm="project_batch_teachers" data-tm-mode="client" data-tm-searchable>
        <template data-tm-col="full_name" data-tm-label="Giảng viên" data-tm-sortable data-tm-filter-type="text">
          <span class="font-medium">{{ value }}</span>
        </template>
        <template data-tm-col="email" data-tm-label="Email" data-tm-sortable></template>
        <template data-tm-col="min_students" data-tm-label="SV tối thiểu" data-tm-sortable></template>
        <template data-tm-col="max_students" data-tm-label="SV tối đa" data-tm-sortable></template>
        <template data-tm-pagination></template>
        <script type="application/json" data-tm-data="project_batch_teachers">
          <?= json_encode([
            'rows' => array_values($teachers),
            'total' => count($teachers),
            'page' => 1,
            'limit' => max(count($teachers), 15),
          ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>
        </script>
      </div>
    <?php endif; ?>
  </div>
</div>
