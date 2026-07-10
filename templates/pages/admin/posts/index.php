<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Bài Viết
  <span class="badge" data-variant="primary">
    <?= $data->getTotal() ?>
  </span>
</h2>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('admin/posts/create') ?>" data-variant="primary" data-size="md" class="btn">
  <i class="fa-solid fa-plus"></i>
  Thêm mới
</a>
<?php $layout->end() ?>
<div class="tm-container" id="posts_table" data-tm="posts_table" data-tm-mode="server" data-tm-selectable
  data-server-table-url="<?= url('api/v1/posts') ?>" data-tm-searchable data-tm-toolbar-target="#posts-table-header">

  <!-- Khai báo phân trang -->
  <template data-tm-pagination></template>

  <!-- Cột ID -->
  <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px"></template>

  <!-- Cột Tiêu đề với Link edit -->
  <template data-tm-col="title" data-tm-label="Tiêu đề" data-tm-sortable>
    <div class="flex flex-col">
      <a href="<?= url('admin/posts/') ?>{{ row.id }}">{{ value }}</a>
      <span>{{ row.slug }}</span>
    </div>
  </template>

  <!-- Cột Tác giả -->
  <template data-tm-col="author_email" data-tm-label="Tác giả" data-tm-filter-type="text"></template>

  <!-- Cột Trạng thái với Badge -->
  <template data-tm-col="status" data-tm-label="Trạng thái" data-tm-align="center" data-tm-filter-type="select"
    data-tm-filter-options='[{"label":"Tất cả","value":""},{"label":"Đã đăng","value":"published"},{"label":"Bản nháp","value":"draft"}]'>
    <span class="badge" data-variant="{{ value === 'published' ? 'primary' : 'secondary' }}">
      {{ value === 'published' ? 'Đã đăng' : 'Bản nháp' }}
    </span>
  </template>

  <!-- Cột Lượt xem -->
  <template data-tm-col="view_count" data-tm-label="Lượt xem" data-tm-align="center" data-tm-sortable></template>

  <!-- Cột Ngày tạo -->
  <template data-tm-col="created_at" data-tm-label="Ngày tạo" data-tm-sortable></template>
</div>

<!-- Bootstrap Data Source -->

<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="posts_table">
  <?= json_encode([
    'rows' => array_map(fn($post) => [
      'id' => $post->id,
      'title' => $post->title,
      'slug' => $post->slug,
      'author_email' => $post->author->email ?? 'N/A',
      'status' => $post->status,
      'view_count' => $post->view_count,
      'created_at' => $post->created_at ? date('d/m/Y H:i', strtotime($post->created_at)) : 'N/A'
    ], $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    console.log(TableManager.get("posts_table"));
  })

  document.querySelector("#posts_table").addEventListener("tm:selection-change", (e) => {
    console.log(e);
  })
</script>
<script type="module" src="<?= url('public/js/pages/admin/server_table.js') ?>"></script>
<?php $layout->end() ?>