<?php
$layout->start("scripts"); ?>
<script>
  window.API_BASE_URL = <?= json_encode(url('api/v1')) ?>;
  window.__studentReferralLetters__ = {
    batchId: <?= json_encode($current['id'] ?? null) ?>,
    baseUrl: <?= json_encode(url('student/internship/' . ($current['id'] ?? '') . '/referral_letters')) ?>,
    currentStudent: {
      batchStudentId: <?= json_encode($batch_student_id) ?>,
      fullName: <?= json_encode($student->full_name ?? '') ?>,
      dob: <?= json_encode($student->dob ?? '') ?>,
      address: <?= json_encode($student->address ?? '') ?>,
      majorName: <?= json_encode($majorName) ?>
    },
    roster: <?= json_encode(array_values(array_map(fn($item) => [
      'batchStudentId' => $item['batch_student_id'],
      'fullName' => $item['full_name'],
      'studentCode' => $item['student_code'],
    ], $batch_students ?? [])), JSON_UNESCAPED_UNICODE) ?>
  };
</script>
<script src="<?= url('public/js/pages/student_dashboard.js') ?>"></script>
<script type="module" src="<?= url('public/js/pages/student_referral_letters_create.js') ?>"></script>
<link rel="stylesheet" href="<?= url('public/css/student_dashboard.css') ?>">
<?php $layout->end() ?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Đăng ký giấy giới thiệu thực tập
</h2>
<p>Điền đầy đủ thông tin để đăng ký giấy giới thiệu trong đợt "<?= htmlspecialchars($current['title'] ?? '') ?>".</p>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('student/internship/' . ($current['id'] ?? '') . '/referral_letters') ?>" data-variant="outline" data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Hủy bỏ
</a>
<button type="submit" form="rl_requestForm" class="btn" data-variant="primary" data-size="md">
  <i class="fa-solid fa-paper-plane mr-2"></i> Gửi đăng ký
</button>
<?php $layout->end() ?>

<?php $layout->start("content"); ?>
<div class="card p-4 shadow-sm">

  <p class="field__label mb-0 font-semibold">Thông tin công ty thực tập</p>

  <form action="<?= url('student/internship/' . $current['id'] . '/referral_letters') ?>" method="POST" id="rl_requestForm">
    <?= csrf_field() ?>

    <div class="field mb-4" data-orientation="horizontal">
      <input type="checkbox" id="rl_is_manual" name="is_manual" value="1" class="field__input">
      <label for="rl_is_manual" class="field__label">Tôi không tìm thấy mã số thuế / Công ty chưa có mã số thuế</label>
    </div>

    <div class="field mb-4">
      <label class="field__label">Mã số thuế</label>
      <div class="field__input-group">
        <input type="text" name="tax_code" id="rl_tax_code" class="field__input">
        <button type="button" id="rl_btnCheckMST" data-variant="outline" data-size="md" class="btn">Kiểm tra</button>
      </div>
      <div id="rl_mstLoading" class="field__description hidden"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải thông tin...</div>
      <div id="rl_mstError" class="field__error hidden"></div>
    </div>

    <div class="field mb-4" data-field-required>
      <label class="field__label">Tên công ty</label>
      <div class="field__suggest-wrapper">
        <input type="text" name="name" id="rl_company_name" class="field__input relative" required autocomplete="off">
        <div id="rl_companySuggestions" class="suggestions-list hidden"></div>
      </div>
    </div>

    <div class="field mb-4" data-field-required>
      <label class="field__label">Địa chỉ</label>
      <textarea name="address" id="rl_company_address" class="field__input" required></textarea>
    </div>

    <hr class="my-6" />

    <div class="mb-4">
      <div>
        <label class="field__label mb-0 font-semibold">Danh sách nhóm sinh viên thực tập</label>
        <div class="text-sm mt-1">Thêm các sinh viên thực tập chung công ty vào chung một nhóm để xuất chung 1 giấy giới thiệu. Tối đa 15 sinh viên.</div>
      </div>
      <div class="flex justify-between items-center my-4 gap-2">
        <select id="rl_rosterStudent" class="field__input">
          <option value="">-- Chọn sinh viên trong đợt --</option>
          <?php foreach (($batch_students ?? []) as $rosterStudent): ?>
            <?php if ((int)$rosterStudent['batch_student_id'] !== (int)$batch_student_id): ?>
              <option value="<?= (int)$rosterStudent['batch_student_id'] ?>"><?= htmlspecialchars($rosterStudent['student_code'] . ' - ' . $rosterStudent['full_name']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
        <button type="button" class="btn shrink-0" data-variant="secondary" data-size="md" id="rl_btnAddStudent">
          <i class="fa-solid fa-plus mr-1"></i> Thêm sinh viên
        </button>
      </div>
      <div id="rl_studentsContainer" class="flex flex-col gap-3">
        <!-- JS will render rows here -->
      </div>
    </div>
  </form>
</div>
<?php $layout->end(); ?>
