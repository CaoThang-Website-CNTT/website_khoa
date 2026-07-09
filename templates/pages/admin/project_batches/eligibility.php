<?php
$batchObj = (object) $batchObj;
$hasPreview = isset($previewData) && $previewData !== null;
?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Xác nhận điều kiện
</h2>
<p>Đợt: <?= htmlspecialchars($batchObj->title) ?></p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("admin/project_batches/{$batchObj->id}") ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<?php $layout->end() ?>

<?php $layout->start('content') ?>

<div class="card mb-6">
  <div class="card__header">
    <h3 class="card__title">Tải lên danh sách sinh viên đủ điều kiện (Excel)</h3>
    <p class="card__description">
      Upload file Excel danh sách sinh viên đủ điều kiện làm đồ án. Cột chứa MSSV phải nằm ở cột B (Cột thứ 2), dữ liệu bắt đầu từ dòng 2.
    </p>
  </div>
  <div class="card__content">

    <form action="<?= url("admin/project_batches/{$batchObj->id}/eligibility/preview") ?>" method="POST" enctype="multipart/form-data" class="flex gap-4 items-center">
      <?= csrf_field() ?>
      <div class="field" data-field-required>
        <input type="file" name="excel_file" class="field__input" accept=".xlsx, .xls">
      </div>
      <button type="submit" class="btn" data-variant="primary" data-size="lg">
        <i class="fa-solid fa-upload"></i> Tải lên & Xem trước
      </button>
    </form>
  </div>
</div>

<?php if ($hasPreview): ?>
  <?php
  $inExcel = $previewData['in_excel'] ?? [];
  $legacy = $previewData['legacy_eligible'] ?? [];
  $ineligible = $previewData['ineligible'] ?? [];
  ?>
  <div class="card mb-6 border">
    <div class="card__body">
      <h3 class="card__title text-warning"><i class="fa-solid fa-triangle-exclamation"></i> Preview Dữ Liệu</h3>
      <p class="card__description">Vui lòng kiểm tra kỹ danh sách dưới đây trước khi XÁC NHẬN. Những sinh viên "Không đủ điều kiện" sẽ bị xóa khỏi nhóm nếu bạn XÁC NHẬN.</p>

      <div class="tabs mb-4" data-tabs data-tabs-id="eligibility-tabs" data-tabs-mode="client" data-tabs-panel-active="tab-in-excel">
        <div class="tabs__list" role="tablist">
          <button type="button" role="tab" aria-selected="true" data-tabs-trigger="tab-in-excel" class="tabs__trigger">
            Đủ điều kiện (Trong file)
            <span class="badge" data-variant="success"><?= count($inExcel) ?></span>
          </button>
          <button type="button" role="tab" aria-selected="false" data-tabs-trigger="tab-legacy" class="tabs__trigger">
            Đủ điều kiện (Kế thừa đợt trước)
            <span class="badge" data-variant="info"><?= count($legacy) ?></span>
          </button>
          <button type="button" role="tab" aria-selected="false" data-tabs-trigger="tab-ineligible" class="tabs__trigger">
            Không đủ điều kiện (Bị loại)
            <span class="badge" data-variant="destructive"><?= count($ineligible) ?></span>
          </button>
        </div>

        <form action="<?= url("admin/project_batches/{$batchObj->id}/eligibility/confirm") ?>" method="POST" id="confirm-eligibility-form">
          <?= csrf_field() ?>

          <div class="tabs__panel mt-4" data-tabs-panel="tab-in-excel" role="tabpanel">
            <div class="tm-container" id="tm-in-excel" data-tm="tm-in-excel" data-tm-mode="client">
              <template data-tm-col="student_code" data-tm-label="MSSV" data-tm-sortable>
                <div class="font-medium">{{ value }}</div>
              </template>
              <template data-tm-col="full_name" data-tm-label="Họ tên"></template>
              <template data-tm-col="classroom_name" data-tm-label="Lớp"></template>
              <script data-tm-data type="application/json">
                <?= json_encode($inExcel) ?>
              </script>
            </div>
          </div>

          <div class="tabs__panel mt-4" data-tabs-panel="tab-legacy" role="tabpanel" hidden>
            <p style="font-size: 0.875rem; color: var(--color-info); margin-bottom: 1rem;">Hệ thống tự động phát hiện những sinh viên này ĐÃ PASS điều kiện ở đợt trước nên mặc định sẽ ĐƯỢC GIỮ LẠI (Đủ điều kiện).</p>
            <div class="tm-container" id="tm-legacy" data-tm="tm-legacy" data-tm-mode="client">
              <template data-tm-col="student_code" data-tm-label="MSSV" data-tm-sortable>
                <div class="font-medium">{{ value }}</div>
              </template>
              <template data-tm-col="full_name" data-tm-label="Họ tên"></template>
              <template data-tm-col="classroom_name" data-tm-label="Lớp"></template>
              <script data-tm-data type="application/json">
                <?= json_encode($legacy) ?>
              </script>
            </div>
          </div>

          <div class="tabs__panel mt-4" data-tabs-panel="tab-ineligible" role="tabpanel" hidden>
            <p class="text-sm mb-4">Danh sách những sinh viên SẼ BỊ LOẠI do không có mặt trong file Excel và không có lịch sử đậu từ đợt trước. Bạn có thể BỎ CHỌN nếu muốn "cứu" sinh viên nào đó.</p>
            <div class="tm-container" id="tm-ineligible" data-tm="tm-ineligible" data-tm-mode="client" data-tm-selectable="true" data-tm-id-key="id">
              <template data-tm-col="student_code" data-tm-label="MSSV" data-tm-sortable>
                <div class="font-medium">{{ value }}</div>
              </template>
              <template data-tm-col="full_name" data-tm-label="Họ tên"></template>
              <template data-tm-col="classroom_name" data-tm-label="Lớp"></template>
              <script data-tm-data type="application/json">
                <?= json_encode($ineligible) ?>
              </script>
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <input type="hidden" name="action" value="confirm_selected">
            <button type="submit" class="btn" data-variant="destructive" data-size="lg" onclick="return prepareIneligibleIds()">
              <i class="fa-solid fa-check-double"></i> Xác nhận và Loại bỏ sinh viên
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function prepareIneligibleIds() {
      if (!confirm('Bạn có chắc chắn muốn xác nhận? Các nhóm có sinh viên bị loại sẽ bị ảnh hưởng.')) {
        return false;
      }

      const form = document.getElementById('confirm-eligibility-form');

      // Lấy danh sách ID đã chọn trong bảng ineligible
      // Table manager client mode tạo các input checkbox có name="tm-ineligible[]"
      // Lấy bằng DOM
      const selectedCheckboxes = document.querySelectorAll('#tm-ineligible input[type="checkbox"][data-tm-row-selector]:checked');

      // Xóa các input ẩn cũ nếu có
      form.querySelectorAll('input[name="ineligible_ids[]"]').forEach(el => el.remove());

      selectedCheckboxes.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ineligible_ids[]';
        input.value = cb.value;
        form.appendChild(input);
      });

      return true;
    }

    // Automatically select all items in ineligible table when loaded
    document.addEventListener('DOMContentLoaded', () => {
      setTimeout(() => {
        const selectAllCb = document.querySelector('#tm-ineligible input[type="checkbox"][data-tm-select-all]');
        if (selectAllCb && !selectAllCb.checked) {
          selectAllCb.click(); // Trigger select all
        }
      }, 500); // Wait for table manager to render
    });
  </script>
<?php endif; ?>

<?php $layout->end() ?>