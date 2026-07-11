<?php

/**
 * @var array $current
 * @var array $letter
 * @var array $students
 */

$statuses = [
  'pending' => ['Chờ duyệt', 'secondary'],
  'approved' => ['Đang xử lý', 'secondary'],
  'completed' => ['Hoàn thành', 'success'],
  'received' => ['Đã nhận', 'success'],
  'rejected' => ['Từ chối', 'destructive'],
  'cancelled' => ['Đã hủy', 'destructive'],
];
[$statusLabel, $statusVariant] = $statuses[$letter['status']] ?? [$letter['status'], 'outline'];
?>
<link rel="stylesheet" href="<?= url('public/css/student_dashboard.css') ?>">

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Chi tiết yêu cầu nhận giấy giới thiệu thực tập tại "<?= htmlspecialchars($letter['company_name']) ?>"
</h2>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('student/internship/' . ($current['id'] ?? '') . '/referral_letters') ?>" data-variant="outline"
  data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<?php if ($letter['status'] === 'pending'): ?>
  <button type="button" class="btn btn-cancel" data-variant="destructive" data-size="md"
    data-modal-trigger="#rl_cancelModal">
    <i class="fa-solid fa-xmark mr-2"></i> Hủy đăng ký
  </button>
<?php endif; ?>
<?php $layout->end() ?>

<div class="detail-layout">
  <!-- CỘT CHÍNH (TRÁI) -->
  <div class="detail-layout__main">

    <div class="card shadow-sm">
      <div class="card__header">
        <h3 class="font-semibold">Thông tin công ty</h3>
      </div>
      <hr class="separator">
      <div class="card__content">
        <div class="field mb-4" data-field-readonly>
          <label class="field__label">Mã số thuế</label>
          <div class="field__input-group">
            <input type="text" class="field__input"
              value="<?= htmlspecialchars($letter['company_tax_code'] ?: 'Không có') ?>" readonly>
          </div>
        </div>

        <div class="field mb-4" data-field-readonly>
          <label class="field__label">Tên công ty</label>
          <input type="text" class="field__input" value="<?= htmlspecialchars($letter['company_name']) ?>" readonly>
        </div>

        <div class="field mb-4" data-field-readonly>
          <label class="field__label">Địa chỉ</label>
          <textarea class="field__input" rows="2"
            readonly><?= htmlspecialchars($letter['company_address']) ?></textarea>
        </div>
      </div>

    </div>

    <div class="card shadow-sm">
      <div class="card__header">
        <h3 class="font-semibold">Thông tin sinh viên thực tập</h3>
      </div>
      <hr class="separator">
      <div class="card__content space-y-2">
        <?php foreach ($students as $index => $st): ?>
          <div class="card p-4">
            <h4 class="font-semibold mb-3">Thành viên #<?= $index + 1 ?></h4>
            <div class="grid grid-cols-1 gap-4">
              <div class="field" data-field-readonly>
                <label class="field__label">Họ và tên</label>
                <input type="text" class="field__input" value="<?= htmlspecialchars($st['full_name']) ?>" readonly>
              </div>
              <div class="field" data-field-readonly>
                <label class="field__label">Ngành học</label>
                <input type="text" class="field__input" value="<?= htmlspecialchars($st['training_program']) ?>" readonly>
              </div>
              <div class="field" data-field-readonly>
                <label class="field__label">Ngày sinh</label>
                <input type="text" class="field__input" value="<?= htmlspecialchars($st['dob'] ?? '') ?>" readonly>
              </div>
              <div class="field" data-field-readonly>
                <label class="field__label">Địa chỉ</label>
                <input type="text" class="field__input" value="<?= htmlspecialchars($st['address'] ?? '') ?>" readonly>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- SIDEBAR -->
  <div class="detail-layout__sidebar">

    <!-- Thông tin cơ bản -->
    <div class="card shadow-sm">
      <div class="card__header">
        <h3 class="font-semibold">Thông tin giấy giới thiệu</h3>
      </div>
      <hr class="separator">
      <div class="card__content p-4 text-sm space-y-4">
        <div class="flex justify-between items-center">
          <span>Mã giấy:</span>
          <span>#<?= $letter['id'] ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span>Trạng thái:</span>
          <span class="badge" data-variant="<?= $statusVariant ?>"><?= $statusLabel ?></span>
        </div>
        <div class="flex justify-between items-center">
          <span>Ngày đăng ký:</span>
          <span><?= date('d/m/Y H:i', strtotime($letter['created_at'])) ?></span>
        </div>
        <?php if (in_array($letter['status'], ['cancelled', 'rejected'], true) && !empty($letter['cancel_reason'])): ?>
          <div class="mt-4 p-3rounded-md">
            <strong><?= $letter['status'] === 'rejected' ? 'Lý do từ chối:' : 'Lý do hủy:' ?></strong><br>
            <?= nl2br(htmlspecialchars($letter['cancel_reason'])) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($letter['status'] === 'pending'): ?>
  <!-- Modal Hủy đăng ký -->
  <div id="rl_cancelModal" class="modal" tabindex="-1" data-state="closed">
    <div class="modal__header">
      <h3 class="modal__title">Xác nhận hủy đăng ký</h3>
      <button class="modal__close" type="button" data-modal-close>
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div>
      <form method="POST"
        action="<?= url("student/internship/{$current['id']}/referral_letters/{$letter['id']}/cancel") ?>"
        id="rl_cancelForm">
        <?= csrf_field() ?>
        <p class="mb-4 text-sm">Bạn có chắc chắn muốn hủy đăng ký xin giấy giới thiệu tới công ty <span class="font-semibold"><?= htmlspecialchars($letter['company_name']) ?></span> không? Hành động này không thể hoàn tác.</p>

        <div class="field">
          <label class="field__label">Lý do hủy (Tùy chọn)</label>
          <textarea name="cancel_reason" class="field__input" rows="3" placeholder="Nhập lý do hủy đăng ký..."></textarea>
        </div>
      </form>
    </div>
    <div class="modal__footer">
      <button type="button" class="btn" data-variant="outline" data-modal-close>Đóng</button>
      <button type="submit" form="rl_cancelForm" class="btn" data-variant="destructive">Đồng ý Hủy</button>
    </div>
  </div>
<?php endif; ?>