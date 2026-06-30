<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Lớp học
  <span class="badge" data-variant="primary">
    <?= $data->getTotal(); ?>
  </span>
</h2>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/classrooms/create') ?>" data-variant="primary" data-size="md" class="btn">
  <i class="fa-solid fa-plus"></i>
  Thêm lớp
</a>
<?php $layout->end() ?>
<div class="card">
  <div class="tm-container" data-tm="classrooms_table" data-tm-mode="server" data-tm-searchable>

    <!-- Cột ID -->
    <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

    <!-- Cột Tên lớp -->
    <template data-tm-col="short_name" data-tm-label="Tên lớp" data-tm-sortable>
      <a href="<?= url('admin/classrooms/') ?>{{ row.id }}" class="font-medium">{{ value }}</a>
    </template>

    <!-- Cột Ngành -->
    <?php
    $majorOptions = [];
    foreach ($majors as $m) {
      $majorOptions[] = ['label' => $m->full_name, 'value' => $m->full_name];
    }
    ?>
    <template data-tm-col="major_name" data-tm-label="Ngành" data-tm-filter-type="select"
      data-tm-filter-options='<?= json_encode($majorOptions, JSON_UNESCAPED_UNICODE) ?>'></template>

    <!-- Cột Số lượng sinh viên -->
    <template data-tm-col="student_count" data-tm-label="Số sinh viên" data-tm-sortable data-tm-width="130px">
      <span class="badge" data-variant="secondary">{{ value }}</span>
    </template>

    <!-- Cột Hành động -->
    <template data-tm-col="actions" data-tm-label="" data-tm-width="100px">
      <div class="flex gap-2 justify-end">
        <a href="<?= url('admin/classrooms/') ?>{{ row.id }}" class="btn" data-variant="outline" data-size="sm">
          <i class="fa-solid fa-pen-to-square"></i>
        </a>
        <button type="button" class="btn btn-delete-classroom" data-variant="destructive" data-size="sm"
          data-classroom-id="{{ row.id }}" data-classroom-name="{{ row.short_name }}"
          data-modal-trigger="#delete-confirm-modal">
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>
    </template>

    <template data-tm-pagination></template>
  </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal" id="delete-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận xóa lớp học</h3>
    <p class="modal__description">
      Bạn có chắc chắn muốn xóa lớp <strong id="delete-classroom-name"></strong>?
      Thao tác này sẽ không thể hoàn tác.
    </p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="delete-confirm-btn" data-variant="destructive" data-size="lg" class="btn" type="button">Xóa</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<!-- Hidden delete form -->
<form id="delete-form" method="POST" class="hidden">
  <?= csrf_field() ?>
</form>

<!-- Bootstrap Data Source -->

<?php $layout->start("scripts") ?>
<script>
  window.API_CLASSROOMS_URL = "<?= url('api/v1/classrooms') ?>";
  window.DELETE_CLASSROOM_URL = "<?= url('admin/classrooms/delete/') ?>";
  window.INITIAL_DATA = <?= json_encode([
                          'rows' => array_map(function ($classroom) {
                            return [
                              'id' => $classroom->id,
                              'short_name' => $classroom->short_name ?? '',
                              'major_name' => $classroom->major->full_name ?? 'Chưa xác định',
                              'student_count' => $classroom->student_count ?? 0,
                              'actions' => '',
                            ];
                          }, $data->getItems()),
                          'total' => $data->getTotal(),
                          'page' => $data->getCurrentPage(),
                          'limit' => $data->getPerPage()
                        ]) ?>;
</script>
<script type="module" src="<?= url('public/js/pages/admin/classrooms/index.js') ?>"></script>
<?php $layout->end() ?>
