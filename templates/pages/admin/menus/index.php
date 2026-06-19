<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Menu
  <span class="badge" data-variant="primary">
    <?= $data->getTotal(); ?>
  </span>
</h2>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('admin/menus/create') ?>" data-variant="primary" data-size="md" class="btn">
  <i class="fa-solid fa-plus"></i>
  Thêm
</a>
<?php $layout->end() ?>
<div class="tm-container" data-tm="menus_table" data-tm-mode="client" data-tm-searchable>

  <!-- Khai báo phân trang -->
  <template data-tm-pagination></template>

  <!-- Cột Tên menu -->
  <template data-tm-col="label" data-tm-label="Tên menu" data-tm-sortable>
    <a href="<?= url('admin/menus/') ?>{{ row.id }}">{{ value }}</a>
  </template>

  <!-- Cột Key -->
  <template data-tm-col="key" data-tm-label="Key" data-tm-sortable>
    <code>{{ value }}</code>
  </template>

  <!-- Cột Loại -->
  <template data-tm-col="is_editable" data-tm-label="Loại" data-tm-align="center">
    <span class="badge" data-variant="{{ value ? 'secondary' : 'primary' }}">
      {{ value ? 'Tuỳ chỉnh' : 'Hệ thống' }}
    </span>
  </template>

  <!-- Cột Số mục -->
  <template data-tm-col="item_count" data-tm-label="Số mục" data-tm-align="center" data-tm-sortable>
    {{ value }} mục
  </template>

  <!-- Cột Mô tả -->
  <template data-tm-col="description" data-tm-label="Mô tả"></template>

</div>

<!-- Bootstrap Data Source -->

<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="menus_table">
  <?= json_encode([
    'rows' => array_map(fn($menu) => [
      'id' => $menu->id,
      'label' => $menu->label ?? 'N/A',
      'key' => $menu->key ?? 'N/A',
      'is_editable' => $menu->isEditable(),
      'item_count' => (is_array($menu->items) ? count($menu->items) : 0),
      'description' => $menu->description ?? 'N/A'
    ], $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>
<?php $layout->end() ?>
