<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Trang CMS
  <span class="badge" data-variant="primary">
    <?= $data->getTotal(); ?>
  </span>
</h2>
<?php $layout->end() ?>

<div class="tm-container" data-tm="cms_pages_table" data-tm-mode="server" data-tm-searchable
  data-server-table-url="<?= url('api/v1/cms_pages') ?>">
  <template data-tm-pagination></template>

  <template data-tm-col="title" data-tm-label="Trang" data-tm-sortable>
    <a href="<?= url('admin/cms-pages/') ?>{{ row.slug }}">{{ value }}</a>
  </template>

  <template data-tm-col="slug" data-tm-label="Slug" data-tm-sortable></template>

  <template data-tm-col="route_path" data-tm-label="Route">
    <span class="text-sm font-medium">{{ value }}</span>
  </template>

  <template data-tm-col="status" data-tm-label="Trạng thái">
    <span class="badge" data-variant="{{ value === 'published' ? 'primary' : 'secondary' }}">
      {{ value === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}
    </span>
  </template>

  <template data-tm-col="updated_at" data-tm-label="Cập nhật" data-tm-sortable></template>
</div>

<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="cms_pages_table">
  <?= json_encode([
    'rows' => array_map(fn($page) => [
      'id' => $page->id,
      'title' => $page->title,
      'slug' => $page->slug,
      'route_path' => $page->route_path,
      'type' => $page->type,
      'status' => $page->status,
      'updated_at' => $page->updated_at,
      'actions' => '',
    ], $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage(),
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
<script type="module" src="<?= url('public/js/pages/admin/server_table.js') ?>"></script>
<?php $layout->end() ?>
