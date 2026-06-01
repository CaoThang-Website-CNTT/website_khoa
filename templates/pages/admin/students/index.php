<!-- Toast khi redirect về đây có set flash (ví dụ: sau khi xóa thành công) -->

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Sinh Viên
        <span class="badge" data-variant="primary">
          <?= $data->getTotal() ?>
        </span>
      </h2>
    </div>

    <div class="flex gap-2">
      <div>
        <a href="<?= url('admin/students/create') ?>" data-variant="primary" data-size="md" class="btn">
          <i class="fa-solid fa-plus"></i>
          Thêm
        </a>
        <a href="<?= url('admin/students/import') ?>" data-variant="outline" data-size="md" class="btn">
          <i class="fa-solid fa-file-import"></i>
          Nhập
        </a>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->
<div class="card">
  <div class="tm-container" data-tm="students_table" data-tm-mode="client" data-tm-searchable>

    <!-- Cột ID -->
    <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

    <!-- Cột Tên -->
    <template data-tm-col="full_name" data-tm-label="Họ tên" data-tm-sortable data-tm-filter-type="text">
      <div class="flex flex-col">
        <a href="<?= url('admin/students/') ?>{{ row.student_id }}" class="font-medium text-primary">{{ value }}</a>
      </div>
    </template>

    <!-- Cột MSSV -->
    <template data-tm-col="student_id" data-tm-label="MSSV" data-tm-sortable data-tm-filter-type="text"></template>

    <!-- Cột Lớp -->
    <?php
    $classOptions = [['label' => 'Tất cả', 'value' => '']];
    foreach ($classrooms as $c) {
      $classOptions[] = ['label' => $c->short_name, 'value' => $c->short_name];
    }
    ?>
    <template data-tm-col="class_name" data-tm-label="Lớp" data-tm-filter-type="select"
      data-tm-filter-options='<?= json_encode($classOptions, JSON_UNESCAPED_UNICODE) ?>'></template>

    <!-- Cột Gender -->
    <template data-tm-col="gender" data-tm-label="Giới tính" data-tm-filter-type="select"
      data-tm-filter-options='[{"label":"Tất cả","value":""},{"label":"Nam","value":"male"},{"label":"Nữ","value":"female"}]'>
      {{ value === 'male' ? 'Nam' : (value === 'female' ? 'Nữ' : value) }}
    </template>

    <!-- Cột DoB -->
    <template data-tm-col="dob" data-tm-label="Ngày sinh" data-tm-sortable></template>

    <!-- Cột Phone -->
    <template data-tm-col="phone" data-tm-label="SĐT" data-tm-filter-type="text"></template>

    <!-- Phân trang -->
    <template data-tm-pagination></template>
  </div>
</div>

<!-- JSON Data Source cho TableManager -->
<script type="application/json" data-tm-data="students_table">
  <?= json_encode([
    'rows' => array_map(function ($student) {
    return [
      'id' => $student->id,
      'full_name' => $student->full_name ?? 'N/A',
      'student_id' => $student->student_id ?? 'N/A',
      'gender' => $student->gender ?? 'N/A',
      'dob' => $student->dob ?? 'N/A',
      'phone' => $student->phone ?? 'N/A',
      'class_name' => $student->classroom->short_name ?? 'N/A'
    ];
  }, $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>