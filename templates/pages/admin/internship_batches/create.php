<?php
$wizardSteps = [
  ['label' => 'Thông tin chung'],
  ['label' => 'Chọn sinh viên'],
  ['label' => 'Chỉ định Giảng viên'],
];
?>

<?php $layout->start("head") ?>
<link rel="stylesheet" href="<?= url('public/css/internship_batch_create.css') ?>">
<?php $layout->end() ?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Thêm đợt thực tập mới</h2>
<!-- Đã có step wizard thì không cần description tại đủ UI Indicator rồi (cho vào không thêm được thông tin bổ ích) -->
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/internship_batches') ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<?php $layout->end() ?>

<div class="w-full">
  <div id="batch-create-step-wizard" class="step-wizard internship-batch-step-wizard"
    data-step-wizard-label="Tiến trình tạo đợt thực tập" aria-live="polite">
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
          <div class="card__content ">
            <div class="field-group">
              <div class="field" data-field-required>
                <label class="field__label" for="title">Tên đợt thực tập</label>
                <input type="text" id="title" class="field__input" name="title"
                  placeholder="Ví dụ: Đợt thực tập hè 2026" required>
              </div>

              <div class="field">
                <label class="field__label" for="description">Mô tả chung (Tùy chọn)</label>
                <textarea id="description" class="field__input" name="description"
                  placeholder="Ghi chú thêm về đợt"></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="field" data-field-required>
                  <label class="field__label" for="start_at">Ngày bắt đầu</label>
                  <input type="date" id="start_at" class="field__input" name="start_at" required>
                </div>

                <div class="field" data-field-required>
                  <label class="field__label" for="end_at">Ngày kết thúc</label>
                  <input type="date" id="end_at" class="field__input" name="end_at" required>
                </div>
              </div>
              <div id="batch-date-suggestions" class="flex flex-wrap gap-2"></div>
            </div>
          </div>
          <hr class="separator" />
          <div class="card__footer internship-form__footer">
            <button type="button" class="btn btn-next" data-variant="primary" data-size="lg">
              Tiếp tục <i class="fa-solid fa-chevron-right "></i>
            </button>
          </div>
        </fieldset>
      </div>
    </div>

    <!-- BƯỚC 2: CHỌN SINH VIÊN -->
    <div class="wizard-step" id="step-2" data-step-wizard-panel="1">
      <div class="card shadow">
        <fieldset class="field__set">
          <div class="card__header">
            <legend class="field__legend">Chọn sinh viên tham gia</legend>
            <p class="field__description">Chọn các lớp học để tải danh sách sinh viên đủ điều kiện.</p>
          </div>
          <hr class="separator" />
          <div class="card__content">

            <div class="field">
              <label class="field__label">Tải lên danh sách sinh viên (.xlsx)</label>
              <input type="file" id="file-upload-students" class="field__input" accept=".xlsx">
              <p class="text-sm mt-1" id="upload-status-text"></p>
              <p class="field__description">
                Lưu ý: Mẫu file yêu cầu cột theo thứ tự (A: STT, B: MSSV, C: Họ đệm, D: Tên, E: Ngày sinh, F: Lớp, G: Số CCCD).
                File chứa tối đa 1000 sinh viên. Dữ liệu từ dòng số 2.
                <a href="<?= url('public/import_students_template.xlsx') ?>" class="btn shrink-0" data-variant="secondary" data-size="sm" download>
                  <i class="fa-solid fa-download"></i>
                  Tải file mẫu
                </a>
              </p>
            </div>

            <!-- TableManager Component Container -->
            <div id="wrapper-table-students-import" class="mt-6" data-state="closed">
              <div class="flex justify-between items-center mb-4">
                <h4 class="font-semibold text-lg">Danh sách xem trước:</h4>
                <div class="flex items-center gap-3">
                  <span class="badge" data-variant="primary" id="selected-students-count">Đã chọn: 0 SV</span>
                </div>
              </div>

              <div class="tm-container" data-tm="table-students-import" data-tm-mode="client" data-tm-selectable="true"
                data-tm-searchable="true" data-tm-filterable="false" data-tm-limit="20" data-tm-strategy="ajax"
                data-tm-id-key="_id">

                <template data-tm-col="stt" data-tm-label="STT" data-tm-sortable data-tm-width="60px"></template>
                <template data-tm-col="student_code" data-tm-label="MSSV" data-tm-sortable data-tm-width="120px">
                  <span class="font-medium font-mono">{{ value }}</span>
                </template>
                <template data-tm-col="full_name" data-tm-label="Họ và tên" data-tm-sortable>
                  <span class="font-medium">{{ value }}</span>
                </template>
                <template data-tm-col="dob" data-tm-label="Ngày sinh" data-tm-width="120px"></template>
                <template data-tm-col="classroom_name" data-tm-label="Lớp" data-tm-sortable data-tm-width="130px">
                  <span class="badge" data-variant="{{ row.is_classroom_invalid ? 'destructive' : 'secondary' }}" title="{{ row.is_classroom_invalid ? 'Lớp này chưa tồn tại trong hệ thống' : '' }}">
                    {{ value }}
                  </span>
                </template>
                <template data-tm-col="national_id" data-tm-label="Số CCCD" data-tm-sortable data-tm-width="140px">
                  <span class="font-medium font-mono">{{ value }}</span>
                </template>
                <template data-tm-pagination></template>
              </div>


            </div>
          </div>
          <hr class="separator" />
          <div class="card__footer internship-form__footer">
            <button type="button" class="btn btn-prev" data-variant="outline" data-size="lg">
              <i class="fa-solid fa-chevron-left "></i> Quay lại
            </button>
            <button type="button" class="btn btn-next" data-variant="primary" data-size="lg">
              Tiếp tục <i class="fa-solid fa-chevron-right "></i>
            </button>
          </div>
        </fieldset>
      </div>
    </div>

    <!-- BƯỚC 3: CHỈ ĐỊNH GIẢNG VIÊN -->
    <div class="wizard-step" id="step-3" data-step-wizard-panel="2">
      <div class="card shadow">
        <fieldset class="field__set">
          <div class="card__header">
            <legend class="field__legend">Chỉ định Giảng viên</legend>
            <p class="field__description">Phân công giảng viên phụ trách sinh viên.</p>
          </div>
          <hr class="separator" />
          <div class="card__content">
            <div class="field">
              <div class="flex justify-between items-center w-full mb-4">
                <label class="field__label mb-0">Chọn các giảng viên hướng dẫn đợt này:</label>
                <div class="flex gap-2">
                  <button type="button" class="btn" data-variant="primary" data-size="lg" id="btn-equalize-quotas"
                    title="Chia đều số lượng SV cho các GV đã chọn">
                    <i class="fa-solid fa-chart-pie"></i> Chia đều
                  </button>
                  <div class="search-bar flex items-center px-4 gap-2 rounded-3xl text-sm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="search-teachers" class="search-bar__input"
                      placeholder="Tìm tên/email giảng viên..." autocomplete="off" autocorrect="off">
                  </div>
                </div>
              </div>
              <div class="flex justify-center items-center mb-4">
                <!-- Validation Quota UI -->
                <div class="quota-summary flex items-center gap-4 px-4 py-2 rounded-md" id="quota-summary-box">
                  <span class="text-xl font-bold" id="total-capacity-text">0 / 0</span>
                  <i class="fa-solid fa-check-circle hidden" id="quota-status-icon"></i>
                </div>
              </div>
              <div class="teacher-selection-container" id="teachers-container">
                <!-- Nhóm theo Bộ môn sẽ được render bởi JS -->
                <span class="text-sm">Đang tải danh sách Giảng viên...</span>
              </div>
            </div>
          </div>
          <hr class="separator" />
          <div class="card__footer internship-form__footer">
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

<!-- Modal xác nhận bỏ qua step 3 -->
<div class="modal" id="skip-step3-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận tạo đợt</h3>
    <p class="modal__description" id="skip-step3-confirm-msg">Bạn chưa hoàn tất chỉ định giảng viên hướng dẫn trong đợt thực tập. Bạn có chắc muốn tiếp tục tạo đợt thực tập này không?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" data-size="lg" type="button">Hủy</button>
    <button id="skip-step3-confirm-btn" data-variant="primary" class="btn" data-size="lg" type="button">Đồng ý</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<?php $layout->start("scripts") ?>
<script>
  window.API_BASE_URL = <?= json_encode(url('api/v1/internship/batches')) ?>;
  window.REDIRECT_URL = <?= json_encode(url('admin/internship_batches/{id}/students')) ?>;
  window.BATCH_CREATE_WIZARD_STEPS = <?= json_encode($wizardSteps, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= url('public/js/table/table_manager.js') ?>" type="module"></script>
<script src="<?= url('public/js/pages/internship_batch_create.js') ?>" type="module"></script>
<?php $layout->end() ?>