<?php

/**
 * View: Quản lý giấy giới thiệu của một Đợt thực tập
 * Route: /admin/internship_batches/{id}/referral_letters
 */
$batch = $batch ?? null;
$letters = $letters ?? [];
?>
<link rel="stylesheet" href="<?= url('public/css/batch_students.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/referral_letters.css') ?>">

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Giấy giới thiệu</h2>
    <p class="title-wrapper__description">Quản lý và duyệt cấp giấy giới thiệu thực tập cho sinh viên đợt thực tập #<?= htmlspecialchars($batch['id']) ?></p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/internship_batches/' . $batch['id']) ?>" data-variant="outline" data-size="lg" class="btn">
      <i class="fa-solid fa-chevron-left"></i> Quay lại
    </a>
<?php $layout->end() ?>

<div class="card">
  <div class="tm-container" data-tm="referral_letters_table" data-tm-mode="client" data-tm-searchable="true"
    data-tm-selectable="true" data-tm-id-key="id">

    <!-- Checkbox column is auto prepended by table-manager -->

    <template data-tm-col="created_at" data-tm-label="Thông tin đăng ký" data-tm-sortable>
      <div class="flex flex-col gap-1">
        <span class="font-medium text-sm">#{{ row.id }}</span>
        <span class="text-xs">{{ row._formatted_date }}</span>
      </div>
    </template>

    <template data-tm-col="student_search" data-tm-label="Sinh viên" data-tm-filter-type="text" data-tm-sortable>
      <div class="flex flex-col gap-1">
        <span class="font-medium text-sm">{{ row.student_full_name }}</span>
        <span class="text-xs">{{ row.student_code }} - {{ row.classroom_name }}</span>
      </div>
    </template>

    <template data-tm-col="company_search" data-tm-label="Công ty" data-tm-filter-type="text" data-tm-sortable>
      <div class="flex flex-col gap-1 max-w-[250px]">
        <span class="font-medium text-sm line-clamp-1" title="{{ row.company_name }}">{{ row.company_name }}</span>
        <span class="text-xs">MST: {{ row.company_tax_code || '--' }}</span>
        <span class="text-xs line-clamp-1" title="{{ row.company_address }}">{{ row.company_address }}</span>
      </div>
    </template>

    <template data-tm-col="company_verified_label" data-tm-label="Trạng thái Công ty" data-tm-filter-type="select"
      data-tm-filter-options='[{"value":"","label":"Tất cả"},{"value":"Đã xác thực","label":"Đã xác thực"},{"value":"Chưa xác thực","label":"Chưa xác thực"}]'>
      <span class="badge" data-variant="{{ row.company_is_verified == 1 ? 'primary' : 'secondary' }}">{{ value }}</span>
    </template>

    <template data-tm-col="status_label" data-tm-label="Trạng thái" data-tm-filter-type="select"
      data-tm-filter-options='[{"value":"","label":"Tất cả"},{"value":"Chờ duyệt","label":"Chờ duyệt"},{"value":"Đã in","label":"Đã in"},{"value":"Đã hủy","label":"Đã hủy"}]'>
      <span class="badge" data-variant="{{ row.status_variant }}">{{ value }}</span>
    </template>

    <template data-tm-col="_actions" data-tm-label="Thao tác" data-tm-width="100px" data-tm-align="right">
      <button type="button" class="btn btn-detail" data-variant="outline" data-size="sm" data-id="{{ row.id }}"
        data-student="{{ row.student_full_name }} - {{ row.student_code }}" data-company-name="{{ row.company_name }}"
        data-company-tax="{{ row.company_tax_code }}" data-company-address="{{ row.company_address }}"
        data-status-label="{{ row.status_label }}" data-status-variant="{{ row.status_variant }}"
        data-reason="{{ row.cancel_reason }}">
        Chi tiết
      </button>
    </template>

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
      <button type="button" class="btn" data-variant="destructive" data-size="md" id="btn-bulk-cancel">
        <i class="fa-solid fa-xmark"></i> Hủy giấy giới thiệu
      </button>
      <button type="button" class="btn" data-variant="primary" data-size="md" id="btn-bulk-approve">
        <i class="fa-solid fa-check"></i> Duyệt & In giấy
      </button>
    </div>
  </div>
</div>

<!-- Modal Chi tiết -->
<div id="rl_detailModal" class="modal-overlay hidden" data-state="closed">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="title text-xl font-semibold">Chi tiết Giấy giới thiệu</h3>
      <button class="modal-close" data-modal-close="#rl_detailModal">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body">
      <div class="grid gap-4">
        <div class="detail-item">
          <div class="text-xs font-semibold mb-1">Sinh viên</div>
          <div id="dt_student" class="font-medium"></div>
        </div>
        <div class="detail-item">
          <div class="text-xs font-semibold mb-1">Công ty tiếp nhận</div>
          <div id="dt_company_name" class="font-medium text-sm"></div>
          <div id="dt_company_tax" class="text-xs mt-1"></div>
          <div id="dt_company_address" class="text-xs mt-1"></div>
        </div>
        <div class="detail-item">
          <div class="text-xs font-semibold mb-1">Trạng thái</div>
          <div id="dt_status"></div>
        </div>
        <div id="dt_cancel_reason_wrapper" class="detail-item hidden">
          <div class="text-xs font-semibold mb-1">Lý do hủy</div>
          <div id="dt_cancel_reason" class="text-sm border-l-2 pl-2"></div>
        </div>
      </div>
    </div>
    <div class="modal-footer flex justify-end gap-2 mt-4">
      <button type="button" class="btn" data-variant="outline" data-size="md"
        data-modal-close="#rl_detailModal">Đóng</button>
    </div>
  </div>
</div>

<!-- Modal Nhập lý do hủy -->
<div id="cancel-reason-modal" class="modal-overlay hidden" data-state="closed">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="title text-xl font-semibold">Hủy giấy giới thiệu</h3>
      <button type="button" class="modal-close" data-modal-close="#cancel-reason-modal">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body py-4">
      <p class="mb-4">Vui lòng nhập lý do hủy cho <span id="cancel-count" class="font-bold">0</span> giấy giới thiệu đã
        chọn:</p>
      <div class="field" data-field-required>
        <label class="field__label">Lý do hủy</label>
        <textarea id="cancel_reason_input" class="field__input" required rows="3"
          placeholder="Nhập lý do hủy..."></textarea>
      </div>
    </div>
    <div class="modal-footer flex justify-end gap-2 mt-4">
      <button type="button" class="btn" data-size="md" data-variant="outline"
        data-modal-close="#cancel-reason-modal">Hủy bỏ</button>
      <button type="button" id="btn-confirm-cancel" class="btn" data-size="md" data-variant="destructive">Xác nhận
        Hủy</button>
    </div>
  </div>
</div>

<!-- Modal Xác nhận Duyệt -->
<div id="approve-confirm-modal" class="modal-overlay hidden" data-state="closed">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="title text-xl font-semibold">Xác nhận Duyệt</h3>
      <button type="button" class="modal-close" data-modal-close="#approve-confirm-modal">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body py-4">
      <p>Bạn có chắc chắn muốn duyệt và in <span id="approve-count" class="font-bold">0</span> giấy giới thiệu đã chọn?
      </p>
      <p class="text-sm mt-2">Lưu ý: Các giấy không ở trạng thái "Chờ duyệt" sẽ bị từ chối/bỏ qua.</p>
    </div>
    <div class="modal-footer flex justify-end gap-2 mt-4">
      <button type="button" class="btn" data-size="md" data-variant="outline"
        data-modal-close="#approve-confirm-modal">Hủy bỏ</button>
      <button type="button" id="btn-confirm-approve" class="btn" data-size="md" data-variant="primary">Xác nhận
        Duyệt</button>
    </div>
  </div>
</div>


<script type="application/json" data-tm-data="referral_letters_table">
  <?php
  $statusMap = [
    'pending' => ['label' => 'Chờ duyệt', 'variant' => 'secondary'],
    'printed' => ['label' => 'Đã in', 'variant' => 'primary'],
    'cancelled' => ['label' => 'Đã hủy', 'variant' => 'destructive']
  ];
  $rows = array_map(function ($rl) use ($statusMap) {
    $st = $statusMap[$rl['status']] ?? ['label' => $rl['status'], 'variant' => 'outline'];
    return [
      'id' => $rl['id'],
      '_formatted_date' => date('d/m/Y H:i', strtotime($rl['created_at'])),
      'created_at' => $rl['created_at'],
      'student_full_name' => $rl['student_full_name'],
      'student_code' => $rl['student_code'],
      'classroom_name' => $rl['classroom_name'] ?? '--',
      'student_search' => $rl['student_full_name'] . ' ' . $rl['student_code'], // for searching
      'company_name' => $rl['company_name'],
      'company_tax_code' => $rl['company_tax_code'],
      'company_address' => $rl['company_address'],
      'company_search' => $rl['company_name'] . ' ' . $rl['company_tax_code'], // for searching
      'company_is_verified' => $rl['company_is_verified'],
      'company_verified_label' => $rl['company_is_verified'] == 1 ? 'Đã xác thực' : 'Chưa xác thực',
      'status' => $rl['status'],
      'status_label' => $st['label'],
      'status_variant' => $st['variant'],
      'cancel_reason' => $rl['cancel_reason'],
    ];
  }, $letters);
  echo json_encode([
    'rows' => $rows,
    'total' => count($rows),
    'page' => 1,
    'limit' => 20
  ], JSON_UNESCAPED_UNICODE);
  ?>
</script>

<script>
  window.API_BASE_URL = '<?= url('api/v1') ?>';
  const BATCH_ID = <?= $batch['id'] ?>;
</script>
<script type="module" src="<?= url('public/js/pages/referral_letters_manager.js') ?>"></script>