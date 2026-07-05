<?php

/**
 * View: Quản lý giấy giới thiệu của một Đợt thực tập
 * Route: /admin/internship_batches/{id}/referral_letters
 */
$batch = $batch ?? null;
$letters = $letters ?? [];
?>
<link rel="stylesheet" href="<?= url('public/css/referral_letters.css') ?>">

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Giấy giới thiệu</h2>
  <p class="title-wrapper__description">Quản lý và duyệt cấp giấy giới thiệu thực tập cho sinh viên đợt thực tập
    "<?= htmlspecialchars($batch['title']) ?>"</p>
  <?php $layout->end() ?>

  <?php $layout->start('actions') ?>
  <a href="<?= url('admin/internship_batches/' . $batch['id']) ?>" data-variant="outline" data-size="lg" class="btn">
    <i class="fa-solid fa-chevron-left"></i> Quay lại
  </a>
  <?php $layout->end() ?>

  <div class="tm-container" data-tm="referral_letters_table" data-tm-mode="client" data-tm-searchable="true"
    data-tm-selectable="true" data-tm-id-key="id">

    <!-- Cột checkbox được TableManager tự động thêm vào đầu bảng -->

    <template data-tm-col="id" data-tm-label="Id" data-tm-sortable>
      <span class="font-medium text-sm">#{{ row.id }}</span>
    </template>

    <template data-tm-col="student_search" data-tm-label="Sinh viên" data-tm-filter-type="text" data-tm-sortable>
      <div class="flex flex-col gap-1">
        <span class="font-medium text-sm">
          {{ row.student_full_name }}
          <span class="badge {{ row.student_count == 1 ? 'hidden' : '' }}" data-variant="primary" data-size="sm">Nhóm {{
            row.student_count }} SV</span>
        </span>
        <span class="text-xs">{{ row.student_code }} - {{ row.classroom_name }}
      </div>
    </template>

    <template data-tm-col="teacher_name" data-tm-label="GVHD" data-tm-filter-type="text" data-tm-sortable>
      <span class="font-medium text-sm">{{ row.teacher_name || 'Chưa có' }}</span>
    </template>

    <template data-tm-col="company_search" data-tm-label="Công ty" data-tm-filter-type="text" data-tm-sortable>
      <span class="font-medium text-sm" title="{{ row.company_name }}">{{ row.company_name }}</span>
    </template>

    <template data-tm-col="status" data-tm-label="Trạng thái" data-tm-filter-type="select"
      data-tm-filter-options='[{"value":"pending","label":"Chờ duyệt"},{"value":"approved","label":"Đang xử lý"},{"value":"completed","label":"Hoàn thành"},{"value":"received","label":"Đã nhận"},{"value":"rejected","label":"Từ chối"},{"value":"cancelled","label":"Đã hủy"}]'>
      <span class="badge" data-variant="{{ row.status_variant }}">{{ row.status_label }}</span>
    </template>

    <template data-tm-col="_actions" data-tm-label="Thao tác" data-tm-width="120px" data-tm-align="right">
      <div class="flex gap-2 justify-end">
        <button type="button" class="btn btn-cancel {{ row.status !== 'pending' ? 'hidden' : '' }}"
          data-variant="destructive" data-size="sm" data-id="{{ row.id }}" title="Từ chối giấy">
          <i class="fa-solid fa-ban"></i>
        </button>
        <button type="button" class="btn btn-approve {{ row.status !== 'pending' ? 'hidden' : '' }}"
          data-variant="primary" data-size="sm" data-id="{{ row.id }}" title="Duyệt giấy">
          <i class="fa-solid fa-check"></i>
        </button>
        <a href="<?= url("admin/internship_batches/{$batch['id']}/referral_letters") ?>/{{ row.id }}/print"
          type="button" target="_blank" class="btn btn-print {{ !row.can_print ? 'hidden' : '' }}"
          data-variant="primary" data-size="sm" title="Xem trước & In">
          <i class="fa-solid fa-print"></i>
        </a>
        <button type="button" class="btn btn-receive {{ row.status !== 'completed' ? 'hidden' : '' }}"
          data-variant="primary" data-size="sm" data-id="{{ row.id }}" data-name="{{ row.student_full_name }}"
          data-phone="{{ row.student_phone }}" data-email="{{ row.student_email }}" title="Xác nhận đã nhận">
          <i class="fa-solid fa-handshake"></i>
        </button>
      </div>
    </template>

    <template data-tm-pagination></template>
  </div>

  <div id="receive-modal" class="modal" tabindex="-1" data-state="closed">
    <div class="modal__header"><h3 class="modal__title">Xác nhận sinh viên đã nhận giấy</h3><button type="button" class="modal__close" data-modal-close><i class="fa-solid fa-xmark"></i></button></div>
    <div class="py-4 flex flex-col gap-3">
      <div class="field"><label class="field__label">Họ tên người nhận</label><input id="recipient_name" class="field__input" required></div>
      <div class="field"><label class="field__label">Số điện thoại</label><input id="recipient_phone" class="field__input" type="tel" required></div>
      <div class="field"><label class="field__label">Email</label><input id="recipient_email" class="field__input" type="email" required></div>
    </div>
    <div class="modal__footer"><button type="button" class="btn" data-variant="outline" data-modal-close>Hủy</button><button type="button" id="btn-confirm-receive" class="btn" data-variant="primary">Xác nhận đã nhận</button></div>
  </div>


  <!-- Modal nhập lý do từ chối -->
  <div id="cancel-reason-modal" class="modal" tabindex="-1" data-state="closed">
    <div class="modal__header">
      <h3 class="modal__title">Từ chối giấy giới thiệu</h3>
      <button type="button" class="modal__close" data-modal-close>
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="py-4">
      <p class="mb-4">Vui lòng nhập lý do từ chối cho <span id="cancel-count" class="font-bold">0</span> giấy giới thiệu đã
        chọn:</p>
      <div class="field" data-field-required>
        <label class="field__label">Lý do từ chối</label>
        <div class="flex flex-wrap gap-2 mb-2">
          <?php
          $adminCancelReasons = [
            'Thông tin công ty không hợp lệ',
            'Thông tin sinh viên không hợp lệ',
            'Hết hạn đăng ký'
          ];
          foreach ($adminCancelReasons as $reason):
            ?>
            <button type="button" class="badge btn-cancel-suggestion" data-variant="outline"><?= $reason ?></button>
          <?php endforeach; ?>
        </div>
        <textarea id="cancel_reason_input" class="field__input" required rows="3"
          placeholder="Hoặc nhập lý do từ chối khác..."></textarea>
      </div>
    </div>
    <div class="modal__footer">
      <button type="button" class="btn" data-size="lg" data-variant="outline" data-modal-close>Hủy bỏ</button>
      <button type="button" id="btn-confirm-cancel" class="btn" data-size="lg" data-variant="destructive">Xác nhận
        từ chối</button>
    </div>
  </div>

  <?php $layout->start("scripts") ?>
  <script type="application/json" data-tm-data="referral_letters_table">
  <?php
  $statusMap = [
    'pending' => ['label' => 'Chờ duyệt', 'variant' => 'secondary'],
    'completed' => ['label' => 'Hoàn thành', 'variant' => 'success'],
    'received' => ['label' => 'Đã nhận', 'variant' => 'success'],
    'cancelled' => ['label' => 'Đã hủy', 'variant' => 'destructive']
  ];
  $statusMap['approved'] = ['label' => 'Đang xử lý', 'variant' => 'secondary'];
  $statusMap['rejected'] = ['label' => 'Từ chối', 'variant' => 'destructive'];
  $rows = array_map(function ($rl) use ($statusMap) {
    $st = $statusMap[$rl['status']] ?? ['label' => $rl['status'], 'variant' => 'outline'];
    return [
      'id' => $rl['id'],
      '_formatted_date' => date('d/m/Y H:i', strtotime($rl['created_at'])),
      'created_at' => $rl['created_at'],
      'student_full_name' => $rl['student_full_name'],
      'student_code' => $rl['student_code'],
      'classroom_name' => $rl['classroom_name'] ?? '--',
      'student_search' => $rl['student_full_name'] . ' ' . $rl['student_code'], // Dữ liệu phục vụ tìm kiếm
      'company_name' => $rl['company_name'],
      'company_tax_code' => $rl['company_tax_code'],
      'company_address' => $rl['company_address'],
      'company_search' => $rl['company_name'] . ' ' . $rl['company_tax_code'], // Dữ liệu phục vụ tìm kiếm
      'company_is_verified' => $rl['company_is_verified'],
      'company_verified_label' => $rl['company_is_verified'] == 1 ? 'Đã xác thực' : 'Chưa xác thực',
      'status' => $rl['status'],
      'can_print' => $rl['status'] === 'approved',
      'printed_at' => $rl['printed_at'],
      'status_label' => $st['label'],
      'status_variant' => $st['variant'],
      'cancel_reason' => $rl['cancel_reason'],
      'student_count' => $rl['student_count'] ?? 1,
      'teacher_name' => $rl['teacher_name'],
      'student_phone' => $rl['student_phone'] ?? '',
      'student_email' => $rl['student_email'] ?? '',
      'recipient_name' => $rl['recipient_name'] ?? '',
      'recipient_phone' => $rl['recipient_phone'] ?? '',
      'recipient_email' => $rl['recipient_email'] ?? '',
      'received_at' => $rl['received_at'] ?? null,
      'received_by_name' => $rl['received_by_name'] ?? null
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
    window.API_BASE_URL = <?= json_encode(url('api/v1')) ?>;
    window.BATCH_ID = <?= json_encode($batch['id']) ?>;
    window.ADMIN_BATCH_URL = <?= json_encode(url('admin/internship_batches/' . $batch['id'])) ?>;
    window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
  </script>
  <script type="module" src="<?= url('public/js/pages/referral_letters_manager.js') ?>"></script>
  <?php $layout->end() ?>
