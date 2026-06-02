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
        Lớp học
        <span class="badge" data-variant="primary">
          <?= $data->count(); ?>
        </span>
      </h2>
    </div>

    <div class="flex gap-2">
      <div>
        <a href="<?= url('admin/classrooms/create') ?>" data-variant="primary" data-size="md" class="btn">
          <i class="fa-solid fa-plus"></i>
          Thêm
        </a>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->
<div class="card">
  <div class="tm-container" data-tm="classrooms_table" data-tm-mode="client" data-tm-searchable>

    <!-- Cột ID -->
    <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

    <!-- Cột Tên lớp -->
    <template data-tm-col="short_name" data-tm-label="Tên lớp" data-tm-sortable data-tm-filter-type="text">
      <a href="<?= url('admin/classrooms/') ?>{{ row.id }}" class="font-medium">{{ value }}</a>
    </template>

    <!-- Cột Ngành -->
    <?php
    $majorOptions = [['label' => 'Tất cả', 'value' => '']];
    foreach ($majors as $m) {
      $majorOptions[] = ['label' => $m->full_name, 'value' => $m->full_name];
    }
    ?>
    <template data-tm-col="major_name" data-tm-label="Ngành" data-tm-filter-type="select"
      data-tm-filter-options='<?= json_encode($majorOptions, JSON_UNESCAPED_UNICODE) ?>'></template>

    <template data-tm-pagination></template>
  </div>
</div>

<!-- JSON Data Source cho TableManager -->
<script type="application/json" data-tm-data="classrooms_table">
  <?= json_encode([
    'rows' => array_map(function ($classroom) {
      return [
        'id' => $classroom->id,
        'short_name' => $classroom->short_name ?? 'N/A',
        'major_name' => $classroom->major->full_name ?? 'N/A',
        'specialization_name' => $classroom->specialization->full_name ?? 'N/A'
      ];
    }, $data->getItems()),
    'total' => $data->count(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>