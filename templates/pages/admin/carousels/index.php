<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
      Carousel
      <span class="badge" data-variant="primary">
        <?= count($data ?? []); ?>
      </span>
    </h2>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/carousels/create') ?>" data-variant="primary" data-size="md" class="btn">
      <i class="fa-solid fa-plus"></i>
      Thêm
    </a>
<?php $layout->end() ?>
<div class="tm-container" data-tm="carousels_table" data-tm-mode="client" data-tm-searchable>

  <template data-tm-pagination></template>

  <template data-tm-col="name" data-tm-label="Tên danh mục" data-tm-sortable>
    <a href="<?= url('admin/carousels/') ?>{{ row.id }}">{{ value }}</a>
  </template>

  <template data-tm-col="slug" data-tm-label="Slug" data-tm-sortable></template>

  <template data-tm-col="is_active" data-tm-label="Trạng thái">
    <span class="badge" data-variant="{{ value ? 'primary' : 'desctructive' }}">
      {{ value ? 'Đang hoạt động' : 'Ngừng hoạt động' }}
    </span>
  </template>
</div>

<script type="application/json" data-tm-data="carousels_table">
  <?= json_encode([
    'rows' => array_map(fn($carousel) => [
      'id' => $carousel->id,
      'name' => $carousel->name,
      'slug' => $carousel->slug,
      'is_active' => $carousel->is_active,
      'created_at' => $carousel->created_at,
      'updated_at' => $carousel->updated_at,
    ], $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>