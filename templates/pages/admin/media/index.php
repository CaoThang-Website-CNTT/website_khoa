<!-- Toast khi redirect về đây có set flash (ví dụ: sau khi xóa thành công) -->

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Hình ảnh
        <span class="badge" data-variant="primary">
          <?= $data->getTotal(); ?>
        </span>
      </h2>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/media/create') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-plus"></i>
        Thêm
      </a>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<div class="tm-container" data-tm="media_table" data-tm-mode="client" data-tm-searchable>

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

<script type="application/json" data-tm-data="media_table">
  <?= json_encode([
    'rows' => array_map(fn($media) => [
      'id' => $media->id,
      'title' => $media->title ?? 'N/A',
      'file_name' => $media->file_name ?? 'N/A',
      'file_path' => url($media->file_path) ?? 'N/A',
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