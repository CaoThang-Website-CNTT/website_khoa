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
<link rel="stylesheet" href="<?= url('public/css/batch_students_assignment.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/export.css') ?>">

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
  <div class="flex justify-between items-center">
    <div>
      <h2 class="title text-2xl font-semibold">Sinh viên & Phân công</h2>
      <p class="text-sm">Quản lý sinh viên và phân công giảng viên hướng dẫn đợt thực tập
        #<?= htmlspecialchars($batch['id']) ?></p>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/internship_batches/' . $batch['id']) ?>" data-variant="outline" data-size="lg"
        class="btn">
        <i class="fa-solid fa-chevron-left"></i> Quay lại
      </a>

      <!-- Toolbar: Phân công tự động -->
      <?php if ($batch['status'] !== 'closed'): ?>
        <button type="button" id="btn-auto-shuffle" class="btn" data-variant="secondary" data-size="lg">
          <i class="fa-solid fa-shuffle"></i> Ngẫu nhiên
        </button>
        <button type="button" id="btn-auto-even" class="btn" data-variant="primary" data-size="lg">
          <i class="fa-solid fa-scale-balanced"></i> Chia đều
        </button>
      <?php endif; ?>

      <button type="button" class="btn js-sidebar-toggle" data-variant="outline" data-size="md" title="Thu gọn/Mở rộng">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>
  </div>
</div>

<div class="detail-layout detail-layout--collapsible">
  <!-- CỘT CHÍNH (2/3): Thống kê tổng và Danh sách sinh viên -->
  <div class="detail-layout__main">

    <!-- Overall Stats -->
    <div class="assignment-stats">
      <div class="stat-box stat-box--primary shadow-sm">
        <div class="stat-box__icon">
          <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-box__content">
          <span class="stat-box__label">Tổng sinh viên</span>
          <div class="stat-box__value" id="stat-total-students">--</div>
        </div>
      </div>

      <div class="stat-box stat-box--success shadow-sm">
        <div class="stat-box__icon">
          <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="stat-box__content">
          <span class="stat-box__label">Đã phân công</span>
          <div class="stat-box__value" id="stat-assigned-students">--</div>
        </div>
      </div>

      <div class="stat-box shadow-sm">
        <div class="stat-box__icon stat-box__icon--muted">
          <i class="fa-solid fa-user-clock"></i>
        </div>
        <div class="stat-box__content">
          <span class="stat-box__label">Chưa phân công</span>
          <div class="stat-box__value" id="stat-unassigned-students">--</div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
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

        <!-- Cột Lớp -->
        <template data-tm-col="classroom_name" data-tm-label="Lớp" data-tm-filter-type="text" data-tm-sortable
          data-tm-width="120px">
          <span class="text-sm font-semibold">{{ value || '--' }}</span>
        </template>

        <!-- Cột Công ty thực tập -->
        <template data-tm-col="company_name" data-tm-label="Công ty thực tập" data-tm-filter-type="text"
          data-tm-sortable data-tm-width="250px">
          <div class="company-cell flex flex-col">
            <span class="font-semibold text-sm" title="{{ value || 'Chưa cập nhật' }}">
              {{ value || 'Chưa cập nhật' }}
            </span>
            <span class="text-xs">
              {{ row.company_tax_code ? 'MST: ' + row.company_tax_code : '' }}
            </span>
            <span class="text-xs" title="{{ row.company_address }}">
              {{ row.company_address || '' }}
            </span>
          </div>
        </template>

        <!-- Cột Giảng viên hướng dẫn -->
        <template data-tm-col="teacher_name" data-tm-label="Giảng viên HD" data-tm-sortable data-tm-width="280px">
          <div class="teacher-cell" data-assignment-id="{{ row.assignment_id || '' }}"
            data-batch-student-id="{{ row.batch_student_id }}" data-teacher-id="{{ row.teacher_id || '' }}">
            <div class="teacher-cell__display">
              <span class="teacher-cell__name text-sm font-semibold">{{ value || 'Chưa phân công' }}</span>
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
  </div>

  <!-- CỘT PHỤ (1/3): Thống kê giảng viên -->
  <aside class="detail-layout__sidebar">
    <div class="card shadow-sm h-full flex flex-col">
      <div class="card__header flex justify-between items-center">
        <h3 class="font-bold text-lg"><i class="fa-solid fa-chalkboard-user mr-2"></i>Giảng viên <span class="badge"
            data-variant="primary" id="supervisor-count">0</span></h3>
      </div>
      <hr class="separator">
      <div id="supervisor-stats-container" class="supervisor-list">
        <!-- Loading state -->
        <div class="flex flex-col items-center justify-center p-8">
          <i class="fa-solid fa-circle-notch fa-spin text-2xl mb-2"></i>
          <span class="text-sm">Đang tải dữ liệu...</span>
        </div>
      </div>
    </div>
  </aside>
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
  <div class="modal-content shadow-xl">
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
  <div class="modal-content shadow-xl">
    <div class="modal-header">
      <h3 class="title text-xl font-semibold">Xác nhận Hủy phân công</h3>
    </div>
    <div class="modal-body py-4">
      <p>Bạn có chắc chắn muốn hủy phân công cho <span id="bulk-unassign-count" class="font-bold">0</span> sinh viên đã
        chọn?</p>
      <p class="text-sm mt-2">Dữ liệu phân công sẽ bị xóa khỏi hệ thống.</p>
    </div>
    <div class="modal-footer flex justify-end gap-2 mt-4">
      <button type="button" id="btn-close-unassign-modal" class="btn" data-size="md" data-variant="outline">Hủy
        bỏ</button>
      <button type="button" id="btn-confirm-bulk-unassign" class="btn" data-size="md" data-variant="destructive">Xác
        nhận Hủy</button>
    </div>
  </div>
</div>

<!-- Modal: Xác nhận Phân công Tự động (Chia đều) -->
<div id="modal-auto-even" class="modal-overlay hidden" data-state="closed">
  <div class="modal-content shadow-xl">
    <div class="modal-header mb-4">
      <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mb-4">
        <i class="fa-solid fa-scale-balanced text-xl"></i>
      </div>
      <h3 class="title text-xl font-semibold">Thuật toán Chia đều</h3>
    </div>
    <div class="modal-body mb-6">
      <p class="mb-3">Hệ thống sẽ thực hiện các bước sau:</p>
      <ul class="flex flex-col gap-2 text-sm">
        <li class="flex gap-2"><i class="fa-solid fa-check text-success mt-1"></i> Tìm tất cả sinh viên chưa có giảng
          viên hướng dẫn.</li>
        <li class="flex gap-2"><i class="fa-solid fa-check text-success mt-1"></i> Ưu tiên phân công cho giảng viên đang
          hướng dẫn ÍT SINH VIÊN NHẤT.</li>
        <li class="flex gap-2"><i class="fa-solid fa-check text-success mt-1"></i> Đảm bảo không vượt quá hạn mức của
          từng giảng viên.</li>
      </ul>
    </div>
    <div class="modal-footer flex justify-end gap-2">
      <button type="button" id="btn-close-even-modal" class="btn" data-size="md" data-variant="outline">Để sau</button>
      <button type="button" id="btn-confirm-auto-even" class="btn" data-size="md" data-variant="primary">Tiến hành
        ngay</button>
    </div>
  </div>
</div>

<!-- Modal: Xác nhận Phân công Tự động (Ngẫu nhiên) -->
<div id="modal-auto-shuffle" class="modal-overlay hidden" data-state="closed">
  <div class="modal-content shadow-xl">
    <div class="modal-header mb-4">
      <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center mb-4">
        <i class="fa-solid fa-shuffle text-orange-600 text-xl"></i>
      </div>
      <h3 class="title text-xl font-semibold">Thuật toán Ngẫu nhiên</h3>
    </div>
    <div class="modal-body mb-6">
      <p class="mb-3">Hệ thống sẽ thực hiện các bước sau:</p>
      <ul class="flex flex-col gap-2 text-sm">
        <li class="flex gap-2"><i class="fa-solid fa-check text-success mt-1"></i> Tìm tất cả sinh viên chưa có giảng
          viên hướng dẫn.</li>
        <li class="flex gap-2"><i class="fa-solid fa-check text-success mt-1"></i> Phân công ngẫu nhiên cho các giảng
          viên còn hạn mức trống.</li>
        <li class="flex gap-2"><i class="fa-solid fa-check text-success mt-1"></i> Đảm bảo không vượt quá hạn mức của
          từng giảng viên.</li>
      </ul>
    </div>
    <div class="modal-footer flex justify-end gap-2">
      <button type="button" id="btn-close-shuffle-modal" class="btn" data-size="md" data-variant="outline">Để
        sau</button>
      <button type="button" id="btn-confirm-auto-shuffle" class="btn" data-size="md" data-variant="primary">Tiến hành
        ngay</button>
    </div>
  </div>
</div>

<script>
  window.BATCH_ID = <?= $batch['id'] ?>;
  window.BATCH_STATUS = '<?= $batch['status'] ?>';
  window.API_BASE_URL = '<?= url('api/v1/internship/batches') ?>';
</script>
<script type="module" src="<?= url('public/js/pages/batch_students_manager.js') ?>"></script>