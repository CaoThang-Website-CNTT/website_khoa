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
        <a href="<?= request()->previous(fallback: 'admin/internship_batches') ?>" data-variant="outline" data-size="lg"
          class="btn">
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
    <i class="fa-solid fa-spinner fa-spin text-3xl text-primary"></i>
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

              <div class="grid grid-cols-2 gap-4">
                <div class="field" data-field-required>
                  <label class="field__label" for="class_of">Niên khóa</label>
                  <input type="number" id="class_of" class="field__input" name="class_of" placeholder="Ví dụ: 23"
                    required>
                </div>

                <div class="field" data-field-required>
                  <label class="field__label" for="level">Bậc học</label>
                  <select id="level" class="field__input" name="level" required>
                    <option value="CĐ">Cao đẳng (CĐ)</option>
                    <option value="CĐN">Cao đẳng nghề (CĐN)</option>
                  </select>
                </div>
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
              <div class="flex justify-between items-center w-full mb-2">
                <label class="field__label mb-0">Chọn nhanh theo Lớp học:</label>
                <div class="search-bar flex items-center px-4 gap-2 rounded-xl text-sm">
                  <i class="fa-brands fa-sistrix"></i>
                  <input type="text" id="search-classrooms" class="search-bar__input" placeholder="Tìm lớp/ngành..."
                    autocomplete="off" autocorrect="off">
                </div>
              </div>
              <div class="flex flex-wrap gap-2 p-3 rounded-md border" id="classrooms-container">
                <!-- Rendered by JS -->
                <span class="text-sm">Đang tải danh sách lớp...</span>
              </div>
            </div>

            <div class="flex justify-between items-center mb-4 gap-4">
              <div class="search-bar search-bar--students flex items-center px-4 gap-2 rounded-xl text-sm mb-2">
                <i class="fa-brands fa-sistrix"></i>
                <input type="text" id="search-students" class="search-bar__input"
                  placeholder="Tìm SV trong bảng (theo MSSV hoặc Tên)..." autocomplete="off" autocorrect="off">
              </div>
              <div>
                <span class="badge" data-variant="primary" id="selected-students-count">Đã chọn: 0 SV</span>
                <button type="button" class="btn" data-size="lg" data-variant="outline" id="btn-open-import-modal"
                  data-modal-trigger="#import-student-ids-modal">
                  <i class="fa-solid fa-file-import"></i> Nhập danh sách MSSV
                </button>
              </div>
            </div>

            <div class="table-wrapper border rounded-md">
              <table class="data-table mb-0">
                <thead>
                  <tr>
                    <th>
                      <input type="checkbox" id="check-all-students">
                    </th>
                    <th>
                      <h6>MSSV</h6>
                    </th>
                    <th>
                      <h6>Họ và Tên</h6>
                    </th>
                    <th>
                      <h6>Số điện thoại</h6>
                    </th>
                    <th>
                      <h6>Lớp</h6>
                    </th>
                  </tr>
                </thead>
                <tbody id="students-tbody">
                  <tr>
                    <td colspan="5" class="text-center py-4">Vui lòng chọn lớp ở trên để tải danh sách sinh viên.</td>
                  </tr>
                </tbody>
              </table>
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
                    <i class="fa-brands fa-sistrix"></i>
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

<!-- Modal Bulk Import MSSV -->
<div class="modal" id="import-student-ids-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Nhập danh sách MSSV</h2>
    <p class="modal__description">Lưu ý: mỗi MSSV phải nằm trên một dòng.</p>
  </div>
  <div class="modal__content">
    <div class="field">
      <textarea id="import-student-ids-textarea" class="field__input textarea--import"
        placeholder="Ví dụ:&#10;2100123&#10;2100456&#10;2100789"></textarea>
    </div>
    <div id="import-results-container" class="mt-4 hidden">
      <div class="grid grid-cols-2 gap-4">
        <div class="bg-green-50 p-3 rounded border border-green-200">
          <h4 class="text-sm font-semibold text-green-800 mb-2">Hợp lệ (<span id="import-valid-count">0</span>)</h4>
          <ul id="import-valid-list" class="text-xs text-green-700 max-h-32 overflow-y-auto pl-4 list-disc"></ul>
        </div>
        <div class="bg-red-50 p-3 rounded border border-red-200">
          <h4 class="text-sm font-semibold text-red-800 mb-2">Không hợp lệ (<span id="import-invalid-count">0</span>)
          </h4>
          <ul id="import-invalid-list" class="text-xs text-red-700 max-h-32 overflow-y-auto pl-4 list-disc"></ul>
        </div>
      </div>
    </div>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Đóng</button>
    <button id="btn-process-import" data-variant="primary" data-size="lg" class="btn" type="button">Kiểm tra &
      Thêm</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<script>
window.API_BASE_URL = '<?= url('api/v1/internship/batches') ?>';
window.REDIRECT_URL = '<?= url('admin/internship_batches/{id}/assignments') ?>';
</script>
<script src="<?= url('public/js/pages/internship_batch_create.js') ?>"></script>