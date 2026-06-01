<?php

/**
 * View: Danh sách sinh viên & Phân công giảng viên
 * Route: /admin/internship_batches/{id}/students
 * 
 * @var array $batch
 */
$batch = $batch ?? null;
?>

<link rel="stylesheet" href="<?= url('public/css/batch_students.css') ?>">

<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (window.toast) {
        window.toast.<?= ($flash['type']) ?>('<?= $flash['title'] ?>', '<?= $flash['desc'] ?? '' ?>');
      }
    });
  </script>
<?php endif; ?>

<div class="title-wrapper mb-6">
  <div class="flex justify-between items-start">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">Sinh viên & Phân công</h2>
      <p>Quản lý sinh viên và phân công giảng viên hướng dẫn đợt thực tập #<?= htmlspecialchars($batch['id']) ?></p>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/internship_batches/' . $batch['id']) ?>" data-variant="outline" data-size="lg"
        class="btn">
        <i class="fa-solid fa-chevron-left"></i>
        Quay lại
      </a>
      <?php if ($batch['status'] !== 'closed'): ?>
        <button type="button" id="btn-auto-assign" class="btn" data-variant="outline" data-size="lg">
          <i class="fa-solid fa-wand-magic-sparkles"></i> Phân công tự động
        </button>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card">
  <div class="tm-container" data-tm="batch_students_table" data-tm-mode="client" data-tm-searchable="true"
    data-tm-selectable="true" data-tm-id-key="batch_student_id">

    <!-- Cột MSSV -->
    <template data-tm-col="student_code" data-tm-label="MSSV" data-tm-filter-type="text" data-tm-sortable
      data-tm-width="120px">
      <span class="text-sm">{{ value }}</span>
    </template>

    <!-- Cột Họ và Tên -->
    <template data-tm-col="student_name" data-tm-label="Họ và Tên" data-tm-filter-type="text" data-tm-sortable>
      <span class="font-medium text-sm">{{ value }}</span>
    </template>

    <!-- Cột Điện thoại -->
    <template data-tm-col="student_phone" data-tm-label="Số điện thoại" data-tm-filter-type="text">
      <span class="text-sm">{{ value || '--' }}</span>
    </template>

    <!-- Cột Lớp -->
    <template data-tm-col="classroom_name" data-tm-label="Lớp" data-tm-filter-type="text" data-tm-sortable
      data-tm-width="120px">
      <span class="text-sm">{{ value || '--' }}</span>
    </template>

    <!-- Cột Công ty thực tập -->
    <template data-tm-col="company_name" data-tm-label="Công ty thực tập" data-tm-filter-type="text" data-tm-sortable
      data-tm-width="250px">
      <div class="company-cell flex flex-col">
        <span class="font-medium text-sm" title="{{ value || 'Chưa cập nhật' }}">
          {{ value || 'Chưa cập nhật' }}
        </span>
        <span class="text-xs text-muted">
          {{ row.company_tax_code ? 'MST: ' + row.company_tax_code : '' }}
        </span>
        <span class="text-xs text-muted" title="{{ row.company_address }}">
          {{ row.company_address || '' }}
        </span>
      </div>
    </template>

    <!-- Cột Giảng viên hướng dẫn -->
    <template data-tm-col="teacher_name" data-tm-label="Giảng viên HD" data-tm-sortable data-tm-width="280px">
      <div class="teacher-cell" data-assignment-id="{{ row.assignment_id || '' }}"
        data-batch-student-id="{{ row.batch_student_id }}">
        <div class="teacher-cell__display">
          <span class="teacher-cell__name text-sm">{{ value || 'Chưa phân công' }}</span>
          <button type="button" class="btn-teacher-edit btn-icon" title="Sửa phân công">
            <i class="fa-solid fa-pen text-xs"></i>
          </button>
        </div>
        <div class="teacher-cell__editor hidden">
          <select class="teacher-cell__select field__input">
            <!-- Rendered via JS -->
          </select>
        </div>
      </div>
    </template>

    <template data-tm-pagination></template>
  </div>
</div>

<!-- Bulk Action Bar -->
<div id="bulk-action-bar" class="bulk-action-bar hidden shadow-lg" data-state="closed">
  <div class="flex justify-between items-center gap-2 px-6 py-3">
    <div class="flex items-center gap-4">
      <span class="badge" data-variant="primary" id="selected-count">Đã chọn: 0</span>
    </div>
    <div class="flex gap-2">
      <button type="button" class="btn" data-variant="outline" data-size="md" id="btn-cancel-selection">Hủy
        chọn</button>
      <button type="button" class="btn" data-variant="destructive" data-size="md" id="btn-bulk-unassign">
        <i class="fa-solid fa-user-minus"></i> Hủy phân công
      </button>
      <button type="button" class="btn" data-variant="primary" data-size="md" id="btn-bulk-assign">
        <i class="fa-solid fa-user-plus"></i> Phân công giảng viên
      </button>
    </div>
  </div>
</div>

<!-- Modal: Phân công hàng loạt -->
<div id="modal-bulk-assign" class="modal-overlay hidden" data-state="closed">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="title text-xl font-semibold">Phân công Giảng viên</h3>
      <p class="text-sm mt-1">Gán <span id="bulk-student-count" class="font-bold">0</span> sinh viên cho giảng viên được
        chọn dưới đây:</p>
    </div>
    <div class="modal-body py-4">
      <div class="field">
        <label class="field__label">Chọn Giảng viên hướng dẫn:</label>
        <select id="bulk-teacher-select" class="field__input">
          <!-- Rendered via JS -->
        </select>
      </div>
    </div>
    <div class="modal-footer flex justify-end gap-2 mt-4">
      <button type="button" id="btn-close-bulk-modal" class="btn" data-size="md" data-variant="outline">Hủy</button>
      <button type="button" id="btn-confirm-bulk-assign" class="btn" data-size="md" data-variant="primary">Xác nhận phân
        công</button>
    </div>
  </div>
</div>

<!-- Modal: Xác nhận Hủy phân công -->
<div id="modal-bulk-unassign" class="modal-overlay hidden" data-state="closed">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="title text-xl font-semibold text-destructive">Xác nhận Hủy phân công</h3>
    </div>
    <div class="modal-body py-4">
      <p>Bạn có chắc chắn muốn hủy phân công cho <span id="bulk-unassign-count" class="font-bold">0</span> sinh viên đã
        chọn?</p>
      <p class="text-sm mt-2">Sau khi hủy, các sinh viên này sẽ không có Giảng viên hướng dẫn.</p>
    </div>
    <div class="modal-footer flex justify-end gap-2 mt-4">
      <button type="button" id="btn-close-unassign-modal" class="btn" data-size="md" data-variant="outline">Hủy
        bỏ</button>
      <button type="button" id="btn-confirm-bulk-unassign" class="btn" data-size="md" data-variant="destructive">Xác
        nhận Hủy</button>
    </div>
  </div>
</div>

<!-- Modal: Phân công tự động -->
<div id="modal-auto-assign" class="modal-overlay hidden" data-state="closed">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="title text-xl font-semibold">Phân công Tự động</h3>
    </div>
    <div class="modal-body py-4">
      <p class="mb-4">Hệ thống sẽ tự động phân công các sinh viên <b>chưa có Giảng viên</b>. Vui lòng chọn thuật toán:
      </p>

      <div class="flex flex-col gap-3">
        <label class="radio-card flex items-start gap-3 p-3 border rounded">
          <input type="radio" name="auto_method" value="auto_even" checked class="mt-1">
          <div>
            <span class="font-medium">Chia đều</span>
            <p class="text-sm mt-1">Ưu tiên chia sinh viên cho GV đang có ít người nhất để đảm bảo cân bằng.</p>
          </div>
        </label>

        <label class="radio-card flex items-start gap-3 p-3 border rounded">
          <input type="radio" name="auto_method" value="auto_shuffle" class="mt-1">
          <div>
            <span class="font-medium">Ngẫu nhiên</span>
            <p class="text-sm mt-1">Phân công ngẫu nhiên sinh viên cho giảng viên còn hạn mức.</p>
          </div>
        </label>
      </div>
    </div>
    <div class="modal-footer flex justify-end gap-2 mt-4">
      <button type="button" id="btn-close-auto-modal" class="btn" data-size="md" data-variant="outline">Hủy</button>
      <button type="button" id="btn-confirm-auto-assign" class="btn" data-size="md" data-variant="primary">Tiến hành
        phân công</button>
    </div>
  </div>
</div>

<script>
  window.BATCH_ID = <?= $batch['id'] ?>;
  window.BATCH_STATUS = '<?= $batch['status'] ?>';
  window.API_BASE_URL = '<?= url('api/v1/internship/batches') ?>';
</script>
<script type="module" src="<?= url('public/js/pages/batch_students_manager.js') ?>"></script>