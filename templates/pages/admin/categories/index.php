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

<div class="card">
  <div class="tm-container" data-tm="categories_table" data-tm-mode="client" data-tm-searchable>

    <!-- Cột ID -->
    <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

    <!-- Cột Tên -->
    <template data-tm-col="name" data-tm-label="Tên danh mục" data-tm-sortable data-tm-filter-type="text">
      <a href="<?= url('admin/categories/') ?>{{ row.id }}" class="font-medium text-primary">{{ value }}</a>
    </template>

    <!-- Cột Slug -->
    <template data-tm-col="slug" data-tm-label="Slug" data-tm-sortable data-tm-filter-type="text"></template>

    <!-- Cột Loại -->
    <template data-tm-col="type" data-tm-label="Loại" data-tm-filter-type="select" 
      data-tm-filter-options='[{"label":"Tất cả","value":""},{"label":"Hệ thống","value":"const"},{"label":"Tùy chỉnh","value":"custom"}]'>
      <span class="badge" data-variant="{{ value === 'const' ? 'primary' : 'secondary' }}">
        {{ value === 'const' ? 'Hệ thống' : 'Tùy chỉnh' }}
      </span>
    </template>

    <!-- Cột Phân cấp -->
    <template data-tm-col="hierarchy" data-tm-label="Cấp bậc" data-tm-filter-type="select" 
      data-tm-filter-options='[{"label":"Tất cả","value":""},{"label":"Danh mục cha","value":"parent"},{"label":"Danh mục con","value":"child"}]'>
      <span class="badge" data-variant="{{ value === 'parent' ? 'primary' : 'secondary' }}">
        {{ value === 'parent' ? 'Cha' : 'Con' }}
      </span>
    </template>

    <!-- Cột Mô tả -->
    <template data-tm-col="description" data-tm-label="Mô tả"></template>

    <template data-tm-pagination></template>
  </div>
</div>

<!-- JSON Data Source cho TableManager -->
<script type="application/json" data-tm-data="categories_table">
  <?= json_encode([
    'rows' => array_map(function($category) {
      return [
        'id' => $category->id,
        'name' => $category->name,
        'slug' => $category->slug ?? 'N/A',
        'type' => $category->type,
        'hierarchy' => !isset($category->parent_id) ? 'parent' : 'child',
        'description' => $category->description ?? 'N/A'
      ];
    }, $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>