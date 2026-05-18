<!-- Toast khi redirect về đây có set flash (ví dụ: sau khi xóa thành công) -->
<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= ($flash['type']) ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>
<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Categories
        <span class="badge" data-variant="primary">
          <?= $data->getTotal(); ?>
        </span>
      </h2>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/categories/create') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-plus"></i>
        Thêm
      </a>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<div class="tm-container" data-tm="categories_table" data-tm-mode="client" data-tm-searchable>

  <template data-tm-pagination></template>

  <template data-tm-col="name" data-tm-label="Tên danh mục" data-tm-sortable>
    <a href="<?= url('admin/categories/') ?>{{ row.id }}">{{ value }}</a>
  </template>

  <template data-tm-col="slug" data-tm-label="Slug" data-tm-sortable></template>

  <template data-tm-col="type" data-tm-label="Loại" data-tm-align="center" data-tm-filter-type="select"
    data-tm-filter-options='[{"label":"Tất cả","value":""},{"label":"Hệ thống","value":"const"},{"label":"Tùy chỉnh","value":"custom"}]'>
    <span class="badge" data-variant="{{ value === 'const' ? 'primary' : 'secondary' }}">
      {{ value === 'const' ? 'Hệ thống' : 'Tùy chỉnh' }}
    </span>
  </template>

  <template data-tm-col="parent_type" data-tm-label="Cấp" data-tm-align="center" data-tm-sortable>
    <span class="badge" data-variant="{{ value === 'parent' ? 'primary' : 'secondary' }}">
      {{ value === 'parent' ? 'Cha' : 'Con' }}
    </span>
  </template>

  <template data-tm-col="description" data-tm-label="Mô tả"></template>

</div>

<script type="application/json" data-tm-data="categories_table">
  <?= json_encode([
    'rows' => array_map(fn($category) => [
      'id' => $category->id,
      'name' => $category->name ?? 'N/A',
      'slug' => $category->slug ?? 'N/A',
      'type' => $category->type ?? 'custom',
      'parent_type' => empty($category->parent_id) ? 'parent' : 'child',
      'description' => $category->description ?? '—'
    ], $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>