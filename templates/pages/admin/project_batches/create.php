<?php
$wizardSteps = [
  ['label' => 'Thông tin chung'],
  ['label' => 'Cấu hình Giảng viên'],
];
?>

<?php $layout->start("head") ?>
<link rel="stylesheet" href="<?= url('public/css/internship_batch_create.css') ?>">
<style>
  /* Re-use internship batch create styles but override some specifics if needed */
</style>
<?php $layout->end() ?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Thêm đợt đồ án tốt nghiệp mới</h2>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/project_batches') ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<?php $layout->end() ?>

<div class="w-full">
  <div id="batch-create-step-wizard" class="step-wizard internship-batch-step-wizard"
    data-step-wizard-label="Tiến trình tạo đợt đồ án" aria-live="polite">
  </div>

  <form id="form-create-batch">
    <!-- BƯỚC 1: THÔNG TIN CHUNG -->
    <div class="wizard-step" id="step-1" data-step-wizard-panel="0">
      <div class="card shadow">
        <fieldset class="field__set">
          <div class="card__header">
            <legend class="field__legend">Thông tin cơ bản</legend>
            <p class="field__description">
              Vui lòng điền đầy đủ thông tin. Những trường có dấu * là bắt buộc.
            </p>
          </div>
          <hr class="separator" />
          <div class="card__content space-y-6">

            <div class="field" data-field-required>
              <label class="field__label" for="title">Tên đợt đồ án</label>
              <input type="text" id="title" class="field__input" name="title"
                placeholder="Ví dụ: Đợt Đồ án tốt nghiệp CNTT Khóa 23" required>
            </div>

            <div class="field">
              <label class="field__label" for="description">Mô tả chung (Tùy chọn)</label>
              <textarea id="description" class="field__input" name="description" rows="3"
                placeholder="Ghi chú thêm về đợt"></textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="min_class_of">Khóa áp dụng (Từ)</label>
                <input type="number" id="min_class_of" class="field__input" name="min_class_of" placeholder="VD: 21" min="1" required>
              </div>
              <div class="field" data-field-required>
                <label class="field__label" for="max_class_of">Khóa áp dụng (Đến)</label>
                <input type="number" id="max_class_of" class="field__input" name="max_class_of" placeholder="VD: 23" min="1" required>
              </div>
              <div class="field" data-field-required>
                <label class="field__label" for="max_aspirations">Số nguyện vọng tối đa</label>
                <input type="number" id="max_aspirations" class="field__input" name="max_aspirations" value="3" min="1" required>
              </div>
            </div>

            <h4 class="font-medium mb-4">Thời gian đề xuất đề tài (Dành cho Giảng viên)</h4>
            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="topic_proposal_start">Bắt đầu đề xuất</label>
                <input type="date" id="topic_proposal_start" class="field__input" name="topic_proposal_start" required>
              </div>
              <div class="field" data-field-required>
                <label class="field__label" for="topic_proposal_end">Kết thúc đề xuất</label>
                <input type="date" id="topic_proposal_end" class="field__input" name="topic_proposal_end" required>
              </div>
            </div>

            <h4 class="font-medium">Thời gian đăng ký đề tài (Dành cho Sinh viên)</h4>
            <p class="text-sm" style="color: var(--muted-foreground);">Có thể điền sau, khi công bố danh sách đề tài cho Sinh viên đăng ký</p>
            <div class="grid grid-cols-2 gap-4">
              <div class="field">
                <label class="field__label" for="registration_start">Bắt đầu đăng ký</label>
                <input type="date" id="registration_start" class="field__input" name="registration_start">
              </div>
              <div class="field">
                <label class="field__label" for="registration_end">Kết thúc đăng ký</label>
                <input type="date" id="registration_end" class="field__input" name="registration_end">
              </div>
            </div>

          </div>
          <hr class="separator" />
          <div class="card__footer internship-form__footer flex justify-end p-4">
            <button type="button" class="btn btn-next" data-variant="primary" data-size="lg">
              Tiếp tục <i class="fa-solid fa-chevron-right "></i>
            </button>
          </div>
        </fieldset>
      </div>
    </div>

    <!-- BƯỚC 2: CẤU HÌNH GIẢNG VIÊN -->
    <div class="wizard-step" id="step-2" data-step-wizard-panel="1">
      <div class="card shadow">
        <fieldset class="field__set">
          <div class="card__header">
            <legend class="field__legend">Danh sách Giảng viên tham gia</legend>
            <p class="field__description">Chọn giảng viên Khoa CNTT và cấu hình số lượng sinh viên tối thiểu/tối đa cho từng người.</p>
          </div>
          <hr class="separator" />
          <div class="card__content">
            <div class="field">
              <div class="flex justify-between items-center w-full mb-4">
                <label class="field__label mb-0">Chọn các giảng viên:</label>
                <div class="flex gap-2">
                  <div class="search-bar flex items-center px-4 gap-2 rounded-3xl text-sm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="search-teachers" class="search-bar__input"
                      placeholder="Tìm tên/email giảng viên..." autocomplete="off" autocorrect="off">
                  </div>
                </div>
              </div>

              <div class="teacher-selection-container" id="teachers-container">
                <span class="text-sm">Đang tải danh sách Giảng viên...</span>
              </div>
            </div>
          </div>
          <hr class="separator" />
          <div class="card__footer internship-form__footer flex justify-between p-4">
            <button type="button" class="btn btn-prev" data-variant="outline" data-size="lg">
              <i class="fa-solid fa-chevron-left "></i> Quay lại
            </button>
            <button type="button" id="btn-submit-batch" class="btn" data-variant="primary" data-size="lg" disabled>
              <i class="fa-solid fa-check "></i> Hoàn tất tạo đợt
            </button>
          </div>
        </fieldset>
      </div>
    </div>
  </form>
</div>

<?php $layout->start("scripts") ?>
<script>
  window.API_BASE_URL = <?= json_encode(url('api/v1/project_batches')) ?>;
  window.REDIRECT_URL = <?= json_encode(url('admin/project_batches')) ?>;
  window.BATCH_CREATE_WIZARD_STEPS = <?= json_encode($wizardSteps, JSON_UNESCAPED_UNICODE) ?>;

  // Cài giá trị mặc định cho class_of min-max
  document.addEventListener('DOMContentLoaded', () => {
    const currentYear = new Date().getFullYear();
    const currentYearShort = currentYear % 100; // e.g. 26
    document.getElementById('min_class_of').value = currentYearShort - 5;
    document.getElementById('max_class_of').value = currentYearShort - 3;
  });
</script>
<script src="<?= url('public/js/pages/project_batch_create.js') ?>" type="module"></script>
<?php $layout->end() ?>