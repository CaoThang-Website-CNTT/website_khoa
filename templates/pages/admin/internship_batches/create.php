<link rel="stylesheet" href="<?= url('public/css/internship_batch_create.css') ?>">

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">Thêm đợt thực tập mới</h2>
      <p>Điền thông tin đợt thực tập mới vào các trường dưới đây</p>
    </div>
    <div class="flex gap-2">
      <div>
        <a href="<?= url('admin/internship_batches') ?>" data-variant="outline" data-size="lg" class="btn">
          <i class="fa-solid fa-chevron-left"></i>
          Quay lại
        </a>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<!-- Tiến trình các bước (Wizard Stepper) -->
<div class="wizard-stepper mb-8 flex justify-between relative">
  <div class="stepper-line"></div>

  <div class="step-item active" data-step="1">
    <div class="step-circle">1</div>
    <div class="step-label">Thông tin chung</div>
  </div>

  <div class="step-item" data-step="2">
    <div class="step-circle">2</div>
    <div class="step-label">Chọn sinh viên</div>
  </div>

  <div class="step-item" data-step="3">
    <div class="step-circle">3</div>
    <div class="step-label">Chỉ định Giảng viên</div>
  </div>
</div>

<div class="w-full">

  <div id="wizard-loader" class="assignment-loader hidden">
    <i class="fa-solid fa-spinner fa-spin text-3xl"></i>
  </div>

  <form id="form-create-batch" class="detail-layout">
    <!-- BƯỚC 1: THÔNG TIN CHUNG -->
    <div class="wizard-step" id="step-1">
      <div class="card shadow">
        <fieldset class="field__set">
          <div class="card__header">
            <legend class="field__legend">Thông tin cơ bản</legend>
            <p class="field__description">
              Vui lòng điền đầy đủ thông tin. Những trường có dấu * là bắt buộc.
            </p>
          </div>
          <hr class="separator" />
          <div class="card__content p-6">
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
            </div>
          </div>
          <hr class="separator" />
          <div class="p-4 flex justify-end">
            <button type="button" class="btn btn-next" data-variant="primary" data-size="lg">
              Tiếp tục <i class="fa-solid fa-arrow-right ml-2"></i>
            </button>
          </div>
        </fieldset>
      </div>
    </div>

    <!-- BƯỚC 2: CHỌN SINH VIÊN -->
    <div class="wizard-step hidden" id="step-2">
      <div class="card shadow">
        <fieldset class="field__set">
          <div class="card__header">
            <legend class="field__legend">Chọn sinh viên tham gia</legend>
            <p class="field__description">Chọn các lớp học để tải danh sách sinh viên đủ điều kiện.</p>
          </div>
          <hr class="separator" />
          <div class="card__content p-6">

            <div class="field mb-6">
              <label class="field__label mb-2">Tải lên danh sách sinh viên (.xlsx)</label>
              <div class="flex gap-4 items-center">
                <input type="file" id="file-upload-students" class="field__input" accept=".xlsx"
                  style="max-width: 400px;">
                <span class="text-sm" id="upload-status-text">Chưa chọn file.</span>
              </div>
              <p class="text-sm mt-2 text-hint">
                Lưu ý: Mẫu file yêu cầu cột theo thứ tự (A: STT, B: MSSV, C: Họ đệm, D: Tên, E: Ngày sinh, F: Lớp). File
                chứa tối đa 1000 sinh viên. Dữ liệu từ dòng số 2.
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
                <template data-tm-col="classroom_name" data-tm-label="Lớp" data-tm-sortable data-tm-width="150px">
                  <span class="badge" data-variant="secondary">{{ value }}</span>
                </template>
                <template data-tm-pagination></template>
              </div>


            </div>
          </div>
          <hr class="separator" />
          <div class="p-4 flex justify-between items-center">
            <button type="button" class="btn btn-prev" data-variant="outline" data-size="lg">
              <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
            </button>
            <button type="button" class="btn btn-next" data-variant="primary" data-size="lg">
              Tiếp tục <i class="fa-solid fa-arrow-right ml-2"></i>
            </button>
          </div>
        </fieldset>
      </div>
    </div>

    <!-- BƯỚC 3: CHỈ ĐỊNH GIẢNG VIÊN -->
    <div class="wizard-step hidden" id="step-3">
      <div class="card shadow mb-24">
        <fieldset class="field__set">
          <div class="card__header">
            <legend class="field__legend">Chỉ định Giảng viên & Hạn mức</legend>
            <p class="field__description">Hạn mức là số lượng sinh viên tối đa mà giảng viên có thể hướng dẫn.</p>
            <p class="field__description">Hạn mức được tự động chia đều dựa trên số SV đã chọn. Hoặc có thể chỉnh
              sửa thủ công.</p>
          </div>
          <hr class="separator" />
          <div class="card__content p-6">
            <div class="field">
              <div class="flex justify-between items-center w-full mb-4">
                <label class="field__label mb-0">Chọn các giảng viên hướng dẫn đợt này:</label>
                <div class="flex gap-2">
                  <button type="button" class="btn" data-variant="outline" data-size="lg" id="btn-equalize-quotas"
                    title="Chia đều số lượng SV cho các GV đã chọn">
                    <i class="fa-solid fa-scale-balanced mr-1"></i> Chia đều
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
                  <span class="font-medium">Tổng số SV / tổng hạn mức của giáo viên đã chọn:</span>
                  <span class="text-xl font-bold" id="total-capacity-text">0 / 0</span>
                  <i class="fa-solid fa-check-circle text-success hidden" id="quota-status-icon"></i>
                </div>
              </div>
              <div class="teacher-selection-container" id="teachers-container">
                <!-- Nhóm theo Bộ môn sẽ được render bởi JS -->
                <span class="text-sm">Đang tải danh sách Giảng viên...</span>
              </div>
            </div>
          </div>
          <hr class="separator" />
          <div class="p-4 flex justify-between items-center">
            <button type="button" class="btn btn-prev" data-variant="outline" data-size="lg">
              <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
            </button>
            <button type="button" id="btn-submit-batch" class="btn" data-variant="primary" data-size="lg" disabled>
              <i class="fa-solid fa-check mr-2"></i> Hoàn tất tạo đợt
            </button>
          </div>
        </fieldset>
      </div>

    </div>

  </form>
</div>

<script>
  window.API_BASE_URL = '<?= url('api/v1/internship/batches') ?>';
  window.REDIRECT_URL = '<?= url('admin/internship_batches/{id}/assignments') ?>';
</script>
<script src="<?= url('public/js/table/table_manager.js') ?>" type="module"></script>
<script src="<?= url('public/js/pages/internship_batch_create.js') ?>" type="module"></script>