<?php

/**
 * View: Quản lý giấy giới thiệu (Sinh viên)
 * Route: /student/internship/{batch_id}/referral_letters
 */
$student = $student ?? null;
$current = $current ?? null;
$referralLetters = $referralLetters ?? [];

$rlData = array_map(function ($rl) {
  $statuses = [
    'pending' => ['Chờ duyệt', 'secondary'], 'approved' => ['Đang xử lý', 'secondary'],
    'completed' => ['Hoàn thành', 'primary'], 'received' => ['Đã nhận', 'success'],
    'rejected' => ['Từ chối', 'destructive'], 'cancelled' => ['Đã hủy', 'destructive'],
  ];
  [$statusLabel, $statusVariant] = $statuses[$rl['status']] ?? [$rl['status'], 'outline'];

  return [
    'id' => $rl['id'],
    'created_at_formatted' => date('d/m/Y H:i', strtotime($rl['created_at'])),
    'company_name' => $rl['company_name'],
    'company_tax_code' => $rl['company_tax_code'],
    'company_address' => $rl['company_address'],
    'status' => $rl['status'],
    'status_label' => $statusLabel,
    'status_variant' => $statusVariant,
    'student_count' => $rl['student_count'] ?? 0,
    'cancel_reason' => $rl['cancel_reason'] ?? '',
    'students_json' => htmlspecialchars(json_encode($rl['students'] ?? []), ENT_QUOTES, 'UTF-8')
  ];
}, $referralLetters);
?>


<link rel="stylesheet" href="<?= url('public/css/student_dashboard.css') ?>">

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Giấy giới thiệu thực tập
</h2>
<p class="title-wrapper__description">Danh sách các giấy giới thiệu bạn đã đăng ký trong đợt "<?= $current['title'] ?? '' ?>".</p>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('student/internship/' . ($current['id'] ?? '')) ?>" data-variant="outline" data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<?php if ($canRequestLetter ?? false): ?>
  <a href="<?= url("student/internship/{$current['id']}/referral_letters/create") ?>" id="btn-request" class="btn" data-variant="primary" data-size="md">
    <i class="fa-solid fa-plus mr-2"></i>
    Đăng ký mới
  </a>
<?php endif; ?>
<?php $layout->end() ?>

<div class="tm-container" data-tm="student_referral_letters_table" data-tm-mode="client" data-tm-searchable
  data-tm-id-key="id">

  <template data-tm-col="id" data-tm-label="Id" data-tm-width="60px">
    <span class="text-sm">{{ row.id }}</span>
  </template>

  <template data-tm-col="created_at" data-tm-label="Ngày đăng ký" data-tm-sortable>
    <span class="text-sm">{{ row.created_at_formatted }}</span>
  </template>

  <template data-tm-col="company_name" data-tm-label="Tên công ty" data-tm-filter-type="text" data-tm-sortable>
    <div class="font-medium" title="{{ row.company_name }}">
      <div>
        {{ row.company_name }}
        <span class="badge {{ row.student_count == 1 ? 'hidden' : '' }}" data-variant="primary" data-size="sm">Nhóm {{ row.student_count }} SV</span>
      </div>
    </div>
  </template>

  <template data-tm-col="status" data-tm-label="Trạng thái" data-tm-filter-type="select"
    data-tm-filter-options='[{"value":"pending","label":"Chờ duyệt"},{"value":"approved","label":"Đang xử lý"},{"value":"completed","label":"Hoàn thành"},{"value":"received","label":"Đã nhận"},{"value":"rejected","label":"Từ chối"},{"value":"cancelled","label":"Đã hủy"}]'>
    <span class="badge" data-variant="{{ row.status_variant }}">{{ row.status_label }}</span>
  </template>

  <template data-tm-col="_actions" data-tm-label="Thao tác" data-tm-width="150px" data-tm-align="right">
    <div class="flex gap-2 justify-end">
      <button type="button" class="btn btn-cancel {{ row.status !== 'pending' ? 'hidden' : '' }}" data-variant="destructive" data-size="sm"
        data-id="{{ row.id }}" data-modal-trigger="#rl_cancelModal">
        <i class="fa-solid fa-xmark"></i> Hủy
      </button>
      <a href="<?= url("student/internship/{$current['id']}/referral_letters") ?>/{{ row.id }}" class="btn btn-detail" data-variant="outline" data-size="sm">
        <i class="fa-solid fa-eye"></i>
      </a>
    </div>
  </template>

  <template data-tm-pagination></template>
</div>

<script type="application/json" data-tm-data="student_referral_letters_table">
  <?= json_encode($rlData) ?>
</script>

<!-- Modal Hủy đăng ký -->
<div id="rl_cancelModal" class="modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Hủy giấy giới thiệu</h3>
    <button class="modal__close" type="button" data-modal-close>
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>
  <div>
    <form method="POST" id="rl_cancelForm">
      <?= csrf_field() ?>
      <p class="mb-4">Bạn có chắc chắn muốn hủy giấy giới thiệu này?</p>
      <div class="field" data-field-required>
        <label class="field__label">Lý do hủy</label>
        <div class="flex flex-wrap gap-2 mb-2">
          <?php
          $studentCancelReasons = [
            'Em muốn đổi công ty khác',
            'Em nhập sai thông tin',
            'Em không còn nhu cầu đăng ký'
          ];
          foreach ($studentCancelReasons as $reason):
          ?>
            <button type="button" class="badge btn-cancel-suggestion" data-variant="outline"><?= $reason ?></button>
          <?php endforeach; ?>
        </div>
        <textarea name="cancel_reason" class="field__input" required rows="3"
          placeholder="Hoặc nhập lý do hủy khác..."></textarea>
      </div>
    </form>
  </div>
  <div class="modal__footer">
    <button type="button" class="btn" data-variant="outline" data-modal-close>Đóng</button>
    <button type="submit" form="rl_cancelForm" class="btn" data-variant="destructive">Hủy giấy giới thiệu</button>
  </div>
</div>

<?php $layout->start("scripts") ?>
<script>
  window.API_BASE_URL = <?= json_encode(url('api/v1')) ?>;
  window.__studentReferralLetters__ = {
    batchId: <?= json_encode($current['id']) ?>,
    baseUrl: <?= json_encode(url("student/internship/{$current['id']}/referral_letters")) ?>,
    currentStudent: {
      fullName: <?= json_encode($student->full_name) ?>,
      dob: <?= json_encode($student->dob) ?>,
      address: <?= json_encode($student->address) ?>,
      majorName: <?= json_encode($majorName) ?>
    }
  };
</script>
<script src="<?= url('public/js/pages/student_dashboard.js') ?>"></script>
<script src="<?= url('public/js/pages/student/dashboard/referral_letters.js') ?>" type="module"></script>
<?php $layout->end() ?>
