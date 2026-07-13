<?php

/**
 * View: Danh sách giảng viên hướng dẫn của một Đợt thực tập
 * Route: /admin/internship_batches/{id}/teachers
 */
use App\Enums\BatchStatus;

$batch = $batch ?? null;
$supervisors = $supervisors ?? [];
$isReadOnly = ($batch['status'] ?? null) === BatchStatus::CLOSED;
?>

<link rel="stylesheet" href="<?= url('public/css/batch_teachers.css') ?>">

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Giảng viên hướng dẫn</h2>
  <p class="title-wrapper__description">Danh sách giảng viên tham gia đợt thực tập
    #<?= htmlspecialchars($batch['id']) ?>
  </p>
  <?php $layout->end() ?>

  <?php $layout->start('actions') ?>
  <a href="<?= url('admin/internship_batches/' . $batch['id']) ?>" data-variant="outline" data-size="lg" class="btn">
    <i class="fa-solid fa-chevron-left"></i> Quay lại
  </a>
  <?php if (!$isReadOnly): ?>
    <button type="button" class="btn" data-variant="primary" data-size="lg" data-modal-trigger="#modal-add-teacher">
      <i class="fa-solid fa-plus"></i> Thêm giảng viên
    </button>
  <?php endif; ?>
  <?php $layout->end() ?>

  <div class="tm-container" data-tm="batch_teachers_table" data-tm-mode="client" data-tm-searchable="true">

    <!-- Cột Giảng viên -->
    <template data-tm-col="teacher_search" data-tm-label="Giảng viên" data-tm-filter-type="text" data-tm-sortable>
      <div class="flex flex-col gap-1">
        <span class="font-medium text-sm">{{ row.display_name }}</span>
        <span class="text-xs">{{ row.department_name || '--' }}</span>
      </div>
    </template>

    <!-- Cột Số điện thoại -->
    <template data-tm-col="phone" data-tm-label="Số điện thoại" data-tm-filter-type="text">
      <span class="text-sm">{{ value || '--' }}</span>
    </template>

    <!-- Cột Email -->
    <template data-tm-col="email" data-tm-label="Email" data-tm-filter-type="text">
      <span class="text-sm">{{ value || '--' }}</span>
    </template>

    <!-- Cột SV đang hướng dẫn -->
    <template data-tm-col="assigned_count" data-tm-label="SV đang HD" data-tm-sortable data-tm-width="100px">
      <span class="text-sm font-medium">{{ value }}</span>
    </template>

    <!-- Cột Hạn mức -->
    <template data-tm-col="max_students" data-tm-label="Hạn mức" data-tm-sortable data-tm-width="140px">
      <div class="quota-cell" data-teacher-id="{{ row.teacher_id }}">
        <span class="quota-cell__display">
          <span class="quota-cell__value font-medium text-sm">{{ value }}</span>
          <?php if (!$isReadOnly): ?>
            <button type="button" class="btn-quota-edit btn-icon" title="Sửa hạn mức">
              <i class="fa-solid fa-pen-to-square text-xs"></i>
            </button>
          <?php endif; ?>
        </span>
        <span class="quota-cell__editor hidden">
          <input type="number" class="quota-cell__input field__input" value="{{ value }}" min="0" max="999">
          <button type="button" class="btn-quota-save btn-icon" title="Lưu">
            <i class="fa-solid fa-check text-xs"></i>
          </button>
          <button type="button" class="btn-quota-cancel btn-icon" title="Hủy">
            <i class="fa-solid fa-xmark text-xs"></i>
          </button>
        </span>
      </div>
    </template>

    <!-- Cột Hành động -->
    <template data-tm-col="actions" data-tm-label="Thao tác" data-tm-width="60px">
      <div class="flex justify-end items-center">
        <!-- Ẩn nút xóa nếu đã có SV đang hướng dẫn -->
        <?php if (!$isReadOnly): ?>
          {{#if !row.assigned_count}}
          <button type="button" class="btn btn-delete-teacher" data-variant="destructive" data-size="sm"
            title="Xóa khỏi đợt" data-teacher-id="{{ row.teacher_id }}" data-teacher-name="{{ row.full_name }}">
            <i class="fa-solid fa-trash"></i>
          </button>
          {{/if}}
        <?php endif; ?>
      </div>
    </template>

    <template data-tm-pagination></template>
  </div>

  <!-- Modal: Thêm Giảng Viên -->
  <div id="modal-add-teacher" class="modal" tabindex="-1" data-state="closed">
    <div class="modal__header">
      <h3 class="modal__title">Thêm giảng viên hướng dẫn</h3>
      <p class="modal__description">Thêm giảng viên và chỉ định hạn mức sinh viên.</p>
    </div>
    <div class="modal__body space-y-4">
      <div class="field">
        <label class="field__label">Tìm kiếm giảng viên</label>
        <div class="teacher-search">
          <input type="text" id="search-teacher-input" class="field__input" placeholder="Nhập tên giảng viên..."
            autocomplete="off">
          <div id="search-teacher-results" class="teacher-search__results hidden shadow shadow-sm">
            <!-- Results will be injected here -->
          </div>
        </div>
        <div id="selected-teachers-container" class="flex flex-col gap-2 mt-4">
          <!-- Selected teachers will be appended here -->
        </div>
      </div>


    </div>
    <div class="modal__footer">
      <button data-modal-close data-variant="outline" class="btn" data-size="lg" type="button">Hủy</button>
      <button id="btn-submit-add-teacher" data-variant="primary" class="btn" data-size="lg" type="button" disabled>Thêm
        giảng viên</button>
    </div>
  </div>

  <!-- Modal: Xác nhận Xóa Giảng Viên -->
  <div id="modal-delete-teacher" class="modal" tabindex="-1" data-state="closed">
    <div class="modal__header">
      <h3 class="modal__title">Xóa giảng viên</h3>
      <p class="modal__description" id="delete-teacher-desc">Bạn có chắc chắn muốn xóa giảng viên này khỏi đợt thực tập không?</p>
    </div>
    <div class="modal__footer">
      <input type="hidden" id="delete-teacher-id">
      <button data-modal-close data-variant="outline" class="btn" data-size="lg" type="button">Hủy</button>
      <button id="btn-submit-delete-teacher" data-variant="destructive" class="btn" data-size="lg"
        type="button">Xóa</button>
    </div>
  </div>

  <!-- JSON Data Source cho TableManager -->

  <?php $layout->start("scripts") ?>
  <script type="application/json" data-tm-data="batch_teachers_table">
  <?php
  $rows = array_map(function ($sup) {
    $displayName = trim(($sup['degree'] ? $sup['degree'] . '. ' : '') . $sup['full_name']);
    return [
      'teacher_id' => $sup['teacher_id'],
      'full_name' => $sup['full_name'],
      'degree' => $sup['degree'],
      'display_name' => $displayName,
      'teacher_search' => $sup['full_name'] . ' ' . ($sup['degree'] ?? ''),
      'department_name' => $sup['department_name'],
      'phone' => $sup['phone'],
      'email' => $sup['email'],
      'assigned_count' => (int) $sup['assigned_count'],
      'max_students' => (int) $sup['max_students'],
    ];
  }, $supervisors);
  echo json_encode([
    'rows' => $rows,
    'total' => count($rows),
    'page' => 1,
    'limit' => 20
  ], JSON_UNESCAPED_UNICODE);
  ?>
</script>
  <script>
    window.API_BASE_URL = <?= json_encode(url('api/v1')) ?>;
    window.BATCH_ID = <?= json_encode($batch['id']) ?>;
    window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
  </script>
  <script src="<?= url('public/js/pages/batch_teachers_manager.js') ?>"></script>
  <?php $layout->end() ?>
