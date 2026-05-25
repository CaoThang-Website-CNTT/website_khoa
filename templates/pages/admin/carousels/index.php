<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= $flash['type'] ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div>
      <h2 class="title text-2xl font-semibold">
        Carousel
        <span class="badge" data-variant="primary">
          <?= count($data ?? []); ?>
        </span>
      </h2>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/carousels/create') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-plus"></i>
        Thêm
      </a>
    </div>
  </div>
</div>
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