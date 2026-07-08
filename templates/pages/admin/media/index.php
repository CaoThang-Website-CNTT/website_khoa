<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Hình ảnh
  <span class="badge" data-variant="primary">
    <?= $data->getTotal(); ?>
  </span>
</h2>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/media/create') ?>" data-variant="primary" data-size="md" class="btn">
  <i class="fa-solid fa-plus"></i>
  Thêm
</a>
<?php $layout->end() ?>
<div class="tm-container" data-tm="media_table" data-tm-mode="server" data-tm-searchable
  data-server-table-url="<?= url('api/v1/media') ?>">

  <template data-tm-pagination></template>

  <template data-tm-col="file_path" data-tm-label="Hình ảnh" data-tm-sortable data-tm-width="80px">
    <img src="{{ value }}" alt="{{ row.alt_text }}" class="w-10 h-10 object-cover">
  </template>

  <template data-tm-col="title" data-tm-label="Tiêu đề" data-tm-sortable>
    <a href="<?= url('admin/media/') ?>{{ row.id }}">{{ value }}</a>
  </template>

  <template data-tm-col="file_name" data-tm-label="Tên File" data-tm-sortable>
    <a href="<?= url('admin/media/') ?>{{ row.id }}">{{ value }}</a>
  </template>

  <template data-tm-col="mime_type" data-tm-label="Loại" data-tm-sortable>
    <span class="badge" data-variant="primary">
      {{ value }}
    </span>
  </template>

  <template data-tm-col="file_size" data-tm-label="Dung lượng" data-tm-sortable>
    {{ value }}
  </template>
</div>

<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="media_table">
  <?= json_encode([
    'rows' => array_map(fn($media) => [
      'id' => $media->id,
      'title' => $media->title ?? 'N/A',
      'file_name' => $media->file_name ?? 'N/A',
      'file_path' => url("public/media/" . $media->file_path) ?? 'N/A',
      'mime_type' => $media->mime_type ?? 'custom',
      'file_size' => $media->file_size ?? 'N/A',
      'alt_text' => $media->alt_text ?? '-',
      'created_at' => $media->created_at ?? 'N/A',
      'updated_at' => $media->updated_at ?? 'N/A',
    ], $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>
<script type="module" src="<?= url('public/js/pages/admin/server_table.js') ?>"></script>
<?php $layout->end() ?>
