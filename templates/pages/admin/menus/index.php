<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper mb-4">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Menu
        <span class="badge" data-variant="primary">
          <?= $data->getTotal(); ?>
        </span>
      </h2>
    </div>
    <div class="flex gap-2 items-center">
      <a href="<?= url('admin/menus/create') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-plus"></i>
        Thêm
      </a>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

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