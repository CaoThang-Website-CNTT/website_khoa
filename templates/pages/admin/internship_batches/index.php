<link rel="stylesheet" href="<?= url('public/css/internship_batches.css') ?>">

<!-- Toast khi redirect về đây có set flash -->
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
    <div class="col-6">
      <h2 class="title text-2xl font-semibold">
        Đợt Thực Tập
        <span class="badge" data-variant="primary">
          <?= $data->getTotal() ?>
        </span>
      </h2>
      <p class="text-sm text-muted-foreground">Quản lý các đợt thực tập của khoa</p>
    </div>

    <div class="flex gap-2">
      <a href="<?= url('admin/internship_batches/create') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-plus"></i>
        Thêm đợt mới
      </a>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<div class="card">
  <div class="tm-container" data-tm="batches_table" data-tm-mode="client" data-tm-searchable>

    <!-- Cột ID -->
    <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

    <!-- Cột Tên đợt -->
    <template data-tm-col="title" data-tm-label="Tên đợt" data-tm-sortable data-tm-filter-type="text">
      <a href="<?= url('admin/internship_batches/') ?>{{ row.id }}" class="font-medium text-primary">{{ value }}</a>
    </template>

    <!-- Cột Khóa -->
    <template data-tm-col="class_of" data-tm-label="Khóa" data-tm-sortable data-tm-filter-type="text"></template>

    <!-- Cột Bậc học -->
    <template data-tm-col="level" data-tm-label="Bậc học" data-tm-filter-type="select"
      data-tm-filter-options='[{"label":"Tất cả","value":""},{"label":"Cao đẳng","value":"Cao đẳng"},{"label":"Đại học","value":"Đại học"}]'></template>

    <!-- Cột Ngày bắt đầu -->
    <template data-tm-col="start_at" data-tm-label="Bắt đầu" data-tm-sortable></template>

    <!-- Cột Ngày kết thúc -->
    <template data-tm-col="end_at" data-tm-label="Kết thúc" data-tm-sortable></template>

    <!-- Cột Trạng thái -->
    <template data-tm-col="status" data-tm-label="Trạng thái" data-tm-filter-type="select"
      data-tm-filter-options='[{"label":"Tất cả","value":""},{"label":"Đang chờ","value":"draft"},{"label":"Đã công bố","value":"published"},{"label":"Đã kết thúc","value":"closed"}]'>
      <span class="badge" data-variant="{{ value === 'published' ? 'primary' : (value === 'closed' ? 'destructive' : 'secondary') }}">
        {{ value === 'published' ? 'Đã công bố' : (value === 'closed' ? 'Đã kết thúc' : 'Đang chờ') }}
      </span>
    </template>

    <template data-tm-pagination></template>
  </div>
</div>

<!-- JSON Data Source cho TableManager -->
<script type="application/json" data-tm-data="batches_table">
  <?= json_encode([
    'rows' => array_map(function ($batch) {
      $b = (object)$batch;
      return [
        'id' => $b->id,
        'title' => $b->title ?? 'N/A',
        'class_of' => $b->class_of ?? 'N/A',
        'level' => $b->level ?? 'N/A',
        'start_at' => $b->start_at ? date('d/m/Y', strtotime($b->start_at)) : 'N/A',
        'end_at' => $b->end_at ? date('d/m/Y', strtotime($b->end_at)) : 'N/A',
        'status' => $b->status ?? 'draft'
      ];
    }, $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>