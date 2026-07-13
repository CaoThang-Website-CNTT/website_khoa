<?php

use App\Enums\ProjectBatchStatus;

$batchObj = (object) $batchObj;
?>

<?php $layout->start('head') ?>
<link rel="stylesheet" href="<?= url('/public/css/allocation.css') ?>">
<link rel="stylesheet" href="<?= url('/public/css/export.css') ?>">
<?php $layout->end() ?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Phân bổ Đề tài
</h2>
<p>Đợt: <?= htmlspecialchars($batchObj->title) ?></p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("admin/project_batches/{$batchObj->id}") ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<?php
$hasErrors = !empty($incompleteGroups);
$hasPreview = isset($previewData) && $previewData !== null;
?>
<button type="button" class="btn" data-variant="primary" data-size="lg" onclick="ModalHandler.instance.open('#auto-allocate-modal')" <?= $hasErrors ? 'disabled' : '' ?>>
  <i class="fa-solid fa-wand-magic-sparkles"></i>
  Phân bổ tự động
</button>
<button type="button" class="btn" data-variant="outline" data-size="lg" onclick="ModalHandler.instance.open('#random-allocate-modal')" <?= $hasErrors ? 'disabled' : '' ?> title="Phân bổ ngẫu nhiên cho các nhóm chưa có đề tài">
  <i class="fa-solid fa-shuffle"></i>
  Phân bổ ngẫu nhiên
</button>
<button type="button" class="btn" data-variant="secondary" data-size="lg" title="Import danh sách sinh viên đủ điều kiện làm đồ án tốt nghiệp" onclick="ModalHandler.instance.open('#import-excel-modal')">
  <i class="fa-solid fa-upload"></i> Import DSSV
</button>

<div id="project-allocations-export-action" class="inline-block"></div>
<?php if (!empty($batchObj->allocation_published_at)): ?>
  <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/unpublish") ?>" method="POST" class="inline-block">
    <?= csrf_field() ?>
    <button type="button" class="btn btn-confirm-action" data-variant="outline" data-size="lg"
      data-confirm-msg="Thu hồi kết quả phân bổ? Sinh viên và giảng viên sẽ không còn xem được kết quả cho đến khi bạn công bố lại."
      data-modal-trigger="#action-confirm-modal">
      <i class="fa-solid fa-eye-slash"></i> Thu hồi
    </button>
  </form>
<?php else: ?>
  <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/publish") ?>" method="POST" class="inline-block">
    <?= csrf_field() ?>
    <?php if (($stats['unassigned'] ?? 0) > 0): ?>
      <input type="hidden" name="force" value="1">
    <?php endif; ?>
    <button type="button" class="btn btn-confirm-action" data-variant="primary" data-size="lg"
      data-confirm-msg="<?= ($stats['unassigned'] ?? 0) > 0 ? "CẢNH BÁO: Còn {$stats['unassigned']} nhóm chưa được phân bổ đề tài. Nếu bạn công bố bây giờ, các nhóm này sẽ thấy kết quả là CHƯA ĐƯỢC PHÂN BỔ. Bạn có đồng ý không?" : "Công bố kết quả phân bổ? Sinh viên và giảng viên sẽ xem được thông tin phân bổ chính thức." ?>"
      data-modal-trigger="#action-confirm-modal">
      <i class="fa-solid fa-bullhorn"></i> Chốt & Công bố
    </button>
  </form>
<?php endif; ?>
<?php $layout->end() ?>

<?php
$topicFilterUrl = function (string $filterKey) use ($batchObj): string {
  $query = ['status' => $filterKey];
  return url("admin/project_batches/{$batchObj->id}/allocation?" . http_build_query($query));
};

$tabsMode = 'navigation';
$tabsId = 'allocation-status-tabs';
$activeTab = $currentFilter ?? 'all';
$tabs = [
  [
    'key' => 'all',
    'label' => 'Tất cả nhóm',
    'href' => $topicFilterUrl('all'),
    'badge' => $stats['total'] ?? 0,
  ],
  [
    'key' => 'assigned',
    'label' => 'Đã có đề tài',
    'href' => $topicFilterUrl('assigned'),
    'badge' => $stats['assigned'] ?? 0,
    'badgeVariant' => 'success',
  ],
  [
    'key' => 'unassigned',
    'label' => 'Chưa có đề tài',
    'href' => $topicFilterUrl('unassigned'),
    'badge' => $stats['unassigned'] ?? 0,
    'badgeVariant' => 'warning',
  ],
  [
    'key' => 'invalid',
    'label' => 'Nhóm cần xử lý',
    'href' => $topicFilterUrl('invalid'),
    'badge' => count($incompleteGroups ?? []),
    'badgeVariant' => 'destructive',
  ]
];

$teacherOptions = [];
foreach ($teachers as $t) {
  $teacherOptions[] = ['value' => $t['id'], 'label' => $t['full_name']];
}
?>

<?php $layout->start('content') ?>

<?php if (!empty($batchObj->allocation_published_at)): ?>
  <div class="alert mb-4" data-variant="warning">
    <div class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div class="alert__content">
      <h3 class="alert__title font-semibold">Đã công bố kết quả cho sinh viên lúc <span class="font-bold"><?= date('H:i d/m/Y', strtotime($batchObj->allocation_published_at)) ?></span></h3>
      <p class="alert__description">
        Mọi thay đổi phân bổ từ thời điểm này sẽ được cập nhật trực tiếp cho sinh viên.
        Hãy thông báo cho sinh viên bị ảnh hưởng nếu có sự thay đổi.
      </p>
    </div>
  </div>
<?php endif; ?>

<?php if ($hasPreview): ?>
  <?php
  $inExcel = $previewData['in_excel'] ?? [];
  $notRegistered = $previewData['eligible_not_registered'] ?? [];
  $ineligible = $previewData['ineligible'] ?? [];
  ?>
  <div class="card mb-6 border">
    <div class="card__header">
      <h3 class="card__title text-warning"><i class="fa-solid fa-triangle-exclamation"></i> Preview Dữ Liệu Import</h3>
      <div class="card__description mt-2">
        <p class="mb-1 text-sm">Vui lòng kiểm tra kỹ số liệu dưới đây trước khi XÁC NHẬN LƯU. Sau khi lưu:</p>
        <ul class="pl-5 text-sm">
          <li>Sinh viên <span class="text-sm font-semibold">Không hợp lệ</span> sẽ bị mất tư cách làm đồ án. Các nhóm chứa sinh viên này sẽ bị đưa vào danh sách 'Cần xử lý thủ công'.</li>
          <li>Đề tài đã phân bổ của các nhóm (nếu có) vẫn được giữ nguyên để bạn tùy ý quyết định thay người hay giải tán nhóm.</li>
        </ul>
      </div>
    </div>
    <hr class="separator">
    <div class="card_content px-4">
      <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/import-confirm") ?>" method="POST" id="confirm-eligibility-form">
        <?= csrf_field() ?>

        <div class="flex gap-4 items-center">
          <button type="submit" class="btn" data-variant="primary" data-size="lg">Xác nhận Lưu Dữ Liệu</button>
          <div class="text-sm">
            Đủ điều kiện (Trong file): <span class="font-semibold"><?= count($inExcel) ?></span> |
            Đủ điều kiện nhưng chưa tạo nhóm: <span class="font-semibold"><?= count($notRegistered) ?></span> |
            KHÔNG đủ điều kiện: <span class="font-semibold"><?= count($ineligible) ?></span>
          </div>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php if ($hasErrors): ?>
  <div class="alert mb-4 flex items-center justify-between" data-variant="error">
    <div class="flex items-start">
      <div class="alert__icon"><i class="fa-solid fa-triangle-exclamation mt-1"></i></div>
      <div class="alert__content">
        <h4 class="alert__title font-semibold">Cảnh báo: Có <?= count($incompleteGroups) ?> nhóm cần xử lý!</h4>
        <p class="alert__description text-sm">Các nhóm này chứa thành viên không đủ điều kiện (hoặc chưa xác nhận). Vui lòng chuyển sang tab <span class="font-semibold text-sm">Nhóm cần xử lý</span> bên dưới để giải quyết.</p>
      </div>
    </div>
    <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/bulk-dissolve-invalid") ?>" method="POST" class="ml-4 flex-shrink-0">
      <?= csrf_field() ?>
      <button type="button" class="btn btn-confirm-action" data-size="md" data-variant="destructive" data-confirm-msg="Bạn có chắc chắn muốn giải tán TẤT CẢ các nhóm có 100% thành viên không đủ điều kiện làm đồ án?" data-modal-trigger="#action-confirm-modal">
        <i class="fa-solid fa-trash-can mr-2"></i> Giải tán hàng loạt
      </button>
    </form>
  </div>
<?php endif; ?>

<div class="flex flex-col justify-between items-start gap-4 mb-4">
  <div class="tabs" data-tabs data-tabs-id="<?= htmlspecialchars($tabsId) ?>"
    data-tabs-mode="<?= htmlspecialchars($tabsMode) ?>" data-tabs-panel-active="<?= htmlspecialchars($activeTab) ?>">
    <div class="tabs__list" role="tablist">
      <?php foreach ($tabs as $tab): ?>
        <?php
        $isActive = ($tab['key'] === $activeTab);
        $badge = $tab['badge'] ?? null;
        ?>
        <a href="<?= htmlspecialchars($tab['href']) ?>" role="tab" aria-selected="<?= $isActive ? 'true' : 'false' ?>"
          data-tabs-trigger="<?= htmlspecialchars($tab['key']) ?>"
          data-tabs-trigger-state="<?= $isActive ? 'active' : 'idle' ?>" tabindex="<?= $isActive ? '0' : '-1' ?>"
          class="tabs__trigger">
          <?= htmlspecialchars($tab['label']) ?>
          <?php if ($badge !== null && $badge > 0): ?>
            <span class="badge" data-variant="<?= htmlspecialchars($tab['badgeVariant'] ?? 'outline') ?>">
              <?= htmlspecialchars((string) $badge) ?>
            </span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="tm-container" id="allocation_table" data-tm="allocation_table" data-tm-mode="server" data-tm-searchable>

  <template data-tm-col="stt" data-tm-label="STT">
    <div class="font-medium">{{ value }}</div>
  </template>

  <template data-tm-col="members" data-tm-label="Thành viên">
    <div class="members-container text-sm" data-group-id="{{ row.id }}"></div>
  </template>

  <template data-tm-col="aspirations" data-tm-label="Nguyện vọng đề tài">
    <div class="aspirations-container text-sm" data-group-id="{{ row.id }}"></div>
  </template>

  <template data-tm-col="assigned_topic_title" data-tm-label="Đề tài phân bổ" data-tm-sortable>
    <div class="{{ value ? '' : 'hidden' }}">
      <div class="font-medium">{{ value }}</div>
    </div>
    <div class="{{ value ? 'hidden' : '' }}">
      <span class="badge" data-variant="destructive">Chưa phân bổ</span>
    </div>
  </template>

  <?php if (!in_array($activeTab, ['unassigned', 'invalid'])): ?>
    <template data-tm-col="teacher_id" data-tm-label="Giảng viên HD" data-tm-filter-type="select" data-tm-filter-options='<?= htmlspecialchars(json_encode($teacherOptions, JSON_UNESCAPED_UNICODE)) ?>'>
      <div class="text-sm {{ row.assigned_teacher_name ? '' : 'text-muted italic' }}">{{ row.assigned_teacher_name || 'Chưa có' }}</div>
    </template>
  <?php endif; ?>

  <template data-tm-col="_actions" data-tm-label="Thao tác" data-tm-align="right">
    <div class="actions-container text-right" data-group-id="{{ row.id }}"></div>
  </template>

  <template data-tm-pagination></template>
</div>

<!-- Auto Allocate Modal -->
<div class="modal" id="auto-allocate-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Phân bổ đề tài tự động</h3>
  </div>
  <div class="modal__content">
    <p>Hệ thống sẽ tự động phân bổ đề tài dựa trên nguyện vọng và thời điểm chốt nguyện vọng.</p>
    <p>Lưu ý: Chỉ các nhóm ĐÃ CHỐT nguyện vọng và tất cả các thành viên đã XÁC NHẬN VÀO NHÓM mới được tham gia phân bổ.</p>
    <div class="alert mt-2" data-variant="info">
      <i class="fa-solid fa-circle-info"></i> Hệ thống sẽ chỉ phân bổ cho các nhóm chưa có đề tài. Các phân bổ trước đó (nếu có) sẽ được giữ nguyên.
    </div>
  </div>
  <div class="modal__footer">
    <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/auto") ?>" method="POST" class="flex justify-end gap-2">
      <?= csrf_field() ?>
      <button data-modal-close type="button" class="btn" data-variant="outline" data-size="lg">Hủy</button>
      <button type="submit" class="btn" data-variant="primary" data-size="lg">Tiến hành</button>
    </form>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Random Allocate Modal -->
<div class="modal" id="random-allocate-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Phân bổ ngẫu nhiên</h3>
  </div>
  <div class="modal__content">
    <p>Hệ thống sẽ tự động phân bổ <span class="font-semibold">ngẫu nhiên</span> các nhóm chưa có đề tài vào các đề tài còn trống.</p>
    <div class="alert mt-2" data-variant="info">
      <i class="fa-solid fa-circle-info"></i> Nên chạy "Phân bổ tự động" trước, sau đó mới dùng tính năng này để xử lý các nhóm còn lại.
    </div>
  </div>
  <div class="modal__footer">
    <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/random") ?>" method="POST" class="flex justify-end gap-2">
      <?= csrf_field() ?>
      <button data-modal-close type="button" class="btn" data-variant="outline" data-size="lg">Hủy</button>
      <button type="submit" class="btn" data-variant="primary" data-size="lg">Tiến hành</button>
    </form>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Manual Assign Modal -->
<div class="modal" id="manual-assign-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Phân bổ thủ công đề tài</h3>
  </div>
  <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/manual") ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="group_id" class="field__input" id="manual_group_id">

    <div class="modal__content">
      <p>Chọn đề tài để phân bổ cho nhóm này. Thao tác này sẽ bỏ qua kiểm tra số lượng sinh viên tối đa của đề tài.</p>

      <div class="field" data-field-required>
        <label for="manual_topic_id" class="field__label">Chọn đề tài</label>
        <select name="topic_id" class="field__input">
          <option value="">-- Chọn đề tài --</option>
          <?php foreach ($topics as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['title']) ?> (<?= htmlspecialchars($t['teacher_name']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="modal__footer">
      <button data-modal-close type="button" class="btn" data-variant="outline" data-size="lg">Hủy</button>
      <button type="button" class="btn btn-confirm-action" data-variant="primary" data-size="lg" data-confirm-msg="Xác nhận phân bổ thủ công đề tài này?" data-modal-trigger="#action-confirm-modal">Xác nhận</button>
    </div>
  </form>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Import Excel Modal -->
<div class="modal" id="import-excel-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Tải lên danh sách sinh viên</h3>
  </div>
  <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/import-preview") ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="modal__content p-4">
      <p class="text-sm">Upload file Excel danh sách sinh viên đủ điều kiện làm đồ án. Cột chứa MSSV phải nằm ở cột thứ 2 (cột B), dữ liệu bắt đầu từ dòng 2.</p>

      <div class="alert mt-4" data-variant="info">
        <p class="mb-2 text-sm">Hệ thống sẽ đối chiếu file này với danh sách sinh viên đã tạo nhóm. Những ai không có tên trong file sẽ bị đánh dấu 'Không đủ điều kiện' (Nhóm của họ sẽ rơi vào trạng thái chờ xử lý).</p>
        <p class="text-sm">Nên thực hiện bước Import này TRƯỚC khi nhấn nút 'Phân bổ tự động'.</p>
      </div>

      <div class="field mt-4" data-field-required>
        <input type="file" name="excel_file" class="field__input" accept=".xlsx, .xls" required>
      </div>
    </div>
    <div class="modal__footer">
      <button data-modal-close type="button" class="btn" data-variant="outline" data-size="lg">Hủy</button>
      <button type="submit" class="btn" data-variant="primary" data-size="lg">Tải lên & Xem trước</button>
    </div>
  </form>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Replace Member Modal -->
<div class="modal" id="replace-member-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Thay thế thành viên</h3>
  </div>
  <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/replace-member") ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="group_id" id="replace_group_id">
    <input type="hidden" name="old_student_id" id="replace_old_student_id">

    <div class="modal__content py-2">
      <div class="field" data-field-required>
        <label for="new_student_id" class="field__label">Chọn sinh viên thay thế</label>
        <select name="new_student_id" class="field__input" required>
          <option value="">-- Chọn sinh viên --</option>
          <?php foreach ($eligibleUnregisteredStudents ?? [] as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?> (<?= htmlspecialchars($s['student_id']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="modal__footer">
      <button data-modal-close type="button" class="btn" data-variant="outline" data-size="lg">Hủy</button>
      <button type="button" class="btn btn-confirm-action" data-variant="primary" data-size="lg" data-confirm-msg="Xác nhận thay thế bằng sinh viên này?" data-modal-trigger="#action-confirm-modal">Xác nhận thay thế</button>
    </div>
  </form>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal xác nhận thao tác -->
<div class="modal" id="action-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận thao tác</h3>
    <p class="modal__description" id="action-confirm-msg">
      Bạn có chắc chắn muốn thực hiện thao tác này?
    </p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="md" class="btn" type="button">Hủy</button>
    <button id="action-confirm-btn" data-variant="primary" data-size="md" class="btn" type="button">Chắc chắn</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<?php $layout->start('scripts') ?>
<script>
  window.API_URL_ALLOCATIONS = "<?= url("api/v1/project_batches/{$batchObj->id}/allocations") ?>";
  window.ALLOCATION_BASE_URL = "<?= url("admin/project_batches/{$batchObj->id}/allocation") ?>";
  window.BATCH_ID = "<?= $batchObj->id ?>";
  window.BATCH_TITLE = <?= json_encode($batchObj->title) ?>;
  window.CSRF_FIELD = `<?= csrf_field() ?>`;
  window.CSRF_TOKEN = "<?= csrf_token() ?>";
</script>
<script src="<?= url('public/js/pages/admin/project_allocations.js') ?>"></script>
<?php $layout->end() ?>