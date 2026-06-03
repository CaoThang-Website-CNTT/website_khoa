<!-- Toast khi redirect về đây có set flash (ví dụ: sau khi xóa thành công) -->

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Giảng Viên
        <span class="badge" data-variant="primary">
          <?= $data->getTotal() ?>
        </span>
      </h2>
    </div>

    <div class="flex gap-2">

      <div>
        <a href="<?= url('admin/teachers/create') ?>" data-variant="primary" data-size="md" class="btn">
          <i class="fa-solid fa-plus"></i>
          Thêm
        </a>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->
<div class="card">
  <div class="tm-container" data-tm="teachers_table" data-tm-mode="client" data-tm-searchable>

    <!-- Cột ID -->
    <template data-tm-col="id" data-tm-label="ID" data-tm-width="80px">#{{ value }}</template>

    <!-- Cột Tên -->
    <template data-tm-col="full_name" data-tm-label="Họ tên" data-tm-sortable data-tm-filter-type="text">
      <a href="<?= url('admin/teachers/') ?>{{ row.id }}" class="font-medium">{{ value }}</a>
    </template>

    <!-- Cột Mã GV -->
    <template data-tm-col="staff_code" data-tm-label="Mã GV" data-tm-sortable data-tm-filter-type="text"></template>

    <!-- Cột Giới tính -->
    <template data-tm-col="gender" data-tm-label="Giới tính" data-tm-filter-type="select"
      data-tm-filter-options='[{"label":"Tất cả","value":""},{"label":"Nam","value":"male"},{"label":"Nữ","value":"female"}]'>
      {{ value === 'male' ? 'Nam' : (value === 'female' ? 'Nữ' : value) }}
    </template>

    <!-- Cột Ngày sinh -->
    <template data-tm-col="dob" data-tm-label="Ngày sinh" data-tm-sortable></template>

    <!-- Cột Chức vụ -->
    <template data-tm-col="position" data-tm-label="Chức vụ" data-tm-filter-type="text"></template>

    <!-- Cột Bộ môn -->
    <template data-tm-col="department" data-tm-label="Bộ môn" data-tm-filter-type="text"></template>

    <!-- Cột Hợp đồng -->
    <template data-tm-col="contract_type" data-tm-label="Hợp đồng" data-tm-filter-type="select"
      data-tm-filter-options='[{"label":"Tất cả","value":""},{"label":"Toàn thời gian","value":"full_time"},{"label":"Bán thời gian","value":"part_time"},{"label":"Thỉnh giảng","value":"visiting"},{"label":"Hợp đồng","value":"contract"}]'>
      <span class="badge" data-variant="primary">
        {{ value === 'full_time' ? 'Toàn thời gian' : (value === 'part_time' ? 'Bán thời gian' : (value === 'visiting' ?
        'Thỉnh giảng' : 'Hợp đồng')) }}
      </span>
    </template>

    <template data-tm-pagination></template>
  </div>
</div>

<!-- JSON Data Source cho TableManager -->
<script type="application/json" data-tm-data="teachers_table">
  <?= json_encode([
    'rows' => array_map(function ($teacher) {
        return [
          'id' => $teacher->id,
          'full_name' => $teacher->full_name ?? 'N/A',
          'staff_code' => $teacher->staff_code ?? 'N/A',
          'gender' => $teacher->gender ?? 'N/A',
          'dob' => $teacher->dob ?? 'N/A',
          'position' => $teacher->position ?? 'N/A',
          'department' => $teacher->department ?? 'N/A',
          'contract_type' => $teacher->contract_type ?? 'N/A'
        ];
      }, $data->getItems()),
    'total' => $data->getTotal(),
    'page' => $data->getCurrentPage(),
    'limit' => $data->getPerPage()
  ]) ?>
</script>