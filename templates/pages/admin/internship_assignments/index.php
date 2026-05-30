<link rel="stylesheet" href="<?= url('public/css/internship_assignments.css') ?>">

<!-- Toast notifications -->
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
        Phân công: <?= htmlspecialchars($batch->title) ?>
      </h2>
      <p class="text-sm">Quản lý phân công sinh viên cho giảng viên hướng dẫn</p>
    </div>

    <div class="flex gap-2 items-center">
      <a href="<?= request()->previous(fallback: url('admin/intership_batches')) ?>" data-variant="outline" data-size="md"
        class="btn">
        <i class="fa-solid fa-chevron-left"></i>
        Quay lại
      </a>
      <button type="button" id="btn-auto-assign" class="btn" data-variant="outline" data-size="md">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Phân công tự động
      </button>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<div class="table-wrapper shadow rounded-md mt-4 relative">
  <div id="table-loader" class="assignment-loader hidden">
    <i class="fa-solid fa-spinner fa-spin text-3xl"></i>
  </div>

  <table class="data-table" id="assignments-table">
    <thead>
      <tr>
        <th width="40">
          <input type="checkbox" id="check-all-students">
        </th>
        <th>
          <h6>MSSV</h6>
        </th>
        <th>
          <h6>Họ và Tên</h6>
        </th>
        <th>
          <h6>Điện thoại</h6>
        </th>
        <th>
          <h6>Lớp</h6>
        </th>
        <th style="min-width: 350px;">
          <h6>Giảng viên hướng dẫn</h6>
        </th>
      </tr>
    </thead>
    <tbody id="assignments-tbody">
      <!-- Rendered via Javascript -->
    </tbody>
  </table>
</div>

<!-- Bulk Action Bar -->
<div id="bulk-action-bar" class="bulk-action-bar hidden shadow-lg">
  <div class="flex justify-between items-center px-6 py-3">
    <div class="flex items-center gap-4">
      <span class="badge" data-variant="primary" id="selected-count">Đã chọn: 0</span>
    </div>
    <div class="flex gap-2">
      <button type="button" class="btn" data-variant="outline" data-size="md" id="btn-cancel-selection">Hủy chọn</button>
      <button type="button" class="btn" data-variant="destructive" data-size="md" id="btn-bulk-unassign">
        <i class="fa-solid fa-user-minus"></i> Hủy phân công
      </button>
      <button type="button" class="btn" data-variant="primary" data-size="md" id="btn-bulk-assign">
        <i class="fa-solid fa-user-plus"></i> Phân công giảng viên
      </button>
    </div>
  </div>
</div>

<!-- Modal Chọn Giảng viên cho Phân công hàng loạt -->
<div id="modal-bulk-assign" class="modal-overlay hidden">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="title text-xl font-semibold">Phân công Giảng viên</h3>
      <p class="text-sm mt-1">Gán <span id="bulk-student-count" class="font-bold">0</span> sinh viên cho giảng viên được chọn dưới đây:</p>
    </div>
    <div class="modal-body py-4">
      <div class="field">
        <label class="field__label">Chọn Giảng viên hướng dẫn:</label>
        <select id="bulk-teacher-select" class="field__input">
          <!-- Rendered via JS -->
        </select>
        <div id="bulk-quota-info" class="mt-2 hidden">
          <!-- Stats for selected teacher -->
        </div>
      </div>
    </div>
    <div class="modal-footer flex justify-end gap-2 mt-4">
      <button type="button" id="btn-close-bulk-modal" class="btn" data-size="md" data-variant="outline">Hủy</button>
      <button type="button" id="btn-confirm-bulk-assign" class="btn" data-size="md" data-variant="primary">Xác nhận phân công</button>
    </div>
  </div>
</div>

<!-- Modal Xác nhận Hủy phân công hàng loạt -->
<div id="modal-bulk-unassign" class="modal-overlay hidden">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="title text-xl font-semibold">Xác nhận Hủy phân công</h3>
    </div>
    <div class="modal-body py-4">
      <p>Bạn có chắc chắn muốn hủy phân công cho <span id="bulk-unassign-count" class="font-bold">0</span> sinh viên đã chọn?</p>
      <p class="text-sm mt-2">Sau khi hủy, các sinh viên này sẽ không có Giảng viên hướng dẫn.</p>
    </div>
    <div class="modal-footer flex justify-end gap-2 mt-4">
      <button type="button" id="btn-close-unassign-modal" class="btn" data-size="md" data-variant="outline">Hủy bỏ</button>
      <button type="button" id="btn-confirm-bulk-unassign" class="btn" data-size="md" data-variant="destructive">Xác nhận Hủy</button>
    </div>
  </div>
</div>

<!-- Modal Chọn Thuật toán Auto Assign -->
<div id="modal-auto-assign" class="modal-overlay hidden">
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
      <button type="button" id="btn-close-modal" class="btn" data-size="md" data-variant="outline">Hủy</button>
      <button type="button" id="btn-confirm-auto-assign" class="btn" data-size="md" data-variant="primary">Tiến hành
        phân công</button>
    </div>
  </div>
</div>

<script>
  window.CURRENT_BATCH_ID = <?= $batchId ?>;
  window.BATCH_STATUS = '<?= $batch->status ?>';
  window.API_BASE_URL = '<?= url('api/v1/internship/batches') ?>';
</script>
<script src="<?= url('public/js/pages/internship_assignments.js') ?>"></script>