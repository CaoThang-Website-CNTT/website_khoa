<?php

use App\Enums\ProjectBatchStatus;

$batchObj = (object) $batchObj;
?>

<?php $layout->start('head') ?>
<link rel="stylesheet" href="<?= url('/public/css/allocation.css') ?>">
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
<button type="button" class="btn" data-variant="secondary" data-size="lg" title="Import danh sách sinh viên đủ điều kiện làm đồ án tốt nghiệp" onclick="ModalHandler.instance.open('#import-excel-modal')">
  <i class="fa-solid fa-upload"></i> Import DSSV
</button>
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
    'badgeVariant' => 'destructive',
  ]
];
?>

<?php $layout->start('content') ?>

<?php if ($hasErrors): ?>
  <div class="alert mb-4" data-variant="error">
    <div class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div class="alert__content">
      <h4 class="alert__title font-semibold">Cảnh báo: Có <?= count($incompleteGroups) ?> nhóm chứa thành viên không đủ điều kiện!</h4>
      <p class="alert__description">Vui lòng xử lý các nhóm không hợp lệ này trước để có thể sử dụng chức năng phân bổ tự động.</p>
    </div>
  </div>

  <div class="card border mb-4" style="border-color: var(--destructive)">
    <div class="card__header flex items-start justify-between">
      <div>
        <h3 class="card__title font-semibold">Các nhóm cần xử lý</h3>
        <p class="card__description">Các sinh viên không đủ điều kiện tham gia làm đồ án sẽ có nền màu đỏ. Bạn cần thao tác thủ công để xử lý</p>
      </div>
      <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/bulk-dissolve-invalid") ?>" method="POST">
        <?= csrf_field() ?>
        <button type="button" class="btn btn-confirm-action" data-size="md" data-variant="destructive" data-confirm-msg="Bạn có chắc chắn muốn giải tán TẤT CẢ các nhóm có 100% thành viên không đủ điều kiện làm đồ án?" data-modal-trigger="#action-confirm-modal">
          <i class="fa-solid fa-trash-can mr-2"></i> Giải tán hàng loạt
        </button>
      </form>
    </div>
    <hr class="separator">
    <div class="card_content" style="overflow-x: auto;">
      <table class="allocation-table">
        <thead class="allocation-table__head">
          <tr class="allocation-table__row">
            <th class="allocation-table__cell allocation-table__cell--header">Mã nhóm</th>
            <th class="allocation-table__cell allocation-table__cell--header">Thành viên</th>
            <th class="allocation-table__cell allocation-table__cell--header">Thao tác xử lý</th>
          </tr>
        </thead>
        <tbody class="allocation-table__body">
          <?php foreach ($incompleteGroups as $ig): ?>
            <tr class="allocation-table__row">
              <td class="allocation-table__cell allocation-table__cell--id">#<?= $ig['id'] ?></td>
              <td class="allocation-table__cell allocation-table__cell--members">
                <?php foreach ($ig['members'] ?? [] as $m): ?>
                  <div class="allocation-table__member <?= !$m['is_eligible'] ? 'allocation-table__member--ineligible' : '' ?>">
                    <div class="flex flex-col">
                      <div><?= htmlspecialchars($m['full_name']) ?> (<?= $m['student_code'] ?>)</div>
                      <?php if (!$m['is_eligible'] && !empty($m['phone'])): ?>
                        <div class="text-xs mt-1">
                          <i class="text-xs fa-solid fa-phone mr-1"></i> <?= htmlspecialchars($m['phone']) ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </td>
              <td class="allocation-table__cell allocation-table__cell--actions">
                <div class="allocation-table__actions">
                  <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/dissolve-group") ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="group_id" value="<?= $ig['id'] ?>">
                    <button type="button" class="btn btn-confirm-action" data-size="md" data-variant="destructive" data-confirm-msg="Bạn có chắc chắn muốn giải tán nhóm này?" data-modal-trigger="#action-confirm-modal">Giải tán nhóm</button>
                  </form>

                  <?php
                  $eligibleCount = 0;
                  $ineligibleCount = 0;
                  $oldStudentId = null;
                  foreach ($ig['members'] ?? [] as $m) {
                    if ($m['is_eligible']) {
                      $eligibleCount++;
                    } else {
                      $ineligibleCount++;
                      $oldStudentId = $m['student_id'];
                    }
                  }
                  ?>
                  <?php if ($eligibleCount == 1 && $ineligibleCount == 1): ?>
                    <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/approve-solo") ?>" method="POST">
                      <?= csrf_field() ?>
                      <input type="hidden" name="group_id" value="<?= $ig['id'] ?>">
                      <button type="button" class="btn btn-confirm-action" data-size="md" data-variant="primary" data-confirm-msg="Xác nhận cho sinh viên này làm đồ án 1 mình?" data-modal-trigger="#action-confirm-modal">Cho phép làm một mình</button>
                    </form>
                    <button type="button" class="btn" data-size="md" data-variant="outline" onclick="openReplaceMemberModal(<?= $ig['id'] ?>, <?= $oldStudentId ?>)">Thay thế thành viên</button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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
      <p class="card__description">Vui lòng kiểm tra kỹ danh sách dưới đây trước khi XÁC NHẬN. Những sinh viên "Không đủ điều kiện" sẽ bị đánh dấu ở các nhóm nếu bạn XÁC NHẬN.</p>
    </div>
    <hr class="separator">
    <div class="card_content px-4">
      <form action="<?= url("admin/project_batches/{$batchObj->id}/allocation/import-confirm") ?>" method="POST" id="confirm-eligibility-form">
        <?= csrf_field() ?>

        <div class="flex gap-4 items-center">
          <button type="submit" class="btn" data-variant="primary" data-size="lg">Xác nhận Lưu Dữ Liệu</button>
          <div class="text-sm">
            Đủ điều kiện (Trong file): <span class="font-semibold"><?= count($inExcel) ?></span> |
            Chưa đăng ký: <span class="font-semibold"><?= count($notRegistered) ?></span> |
            Bị loại: <span class="font-semibold"><?= count($ineligible) ?></span>
          </div>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>

<div class="tabs mb-4" data-tabs data-tabs-id="<?= htmlspecialchars($tabsId) ?>"
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

<div class="tm-container" id="allocation_table" data-tm="allocation_table" data-tm-mode="client" data-tm-searchable>

  <template data-tm-col="id" data-tm-label="Mã nhóm" data-tm-sortable>
    <div class="font-medium">#{{ value }}</div>
  </template>

  <template data-tm-col="members" data-tm-label="Thành viên">
    <div class="members-container text-sm" data-group-id="{{ row.id }}"></div>
  </template>

  <template data-tm-col="aspirations" data-tm-label="Đề tài đăng ký">
    <div class="aspirations-container text-sm" data-group-id="{{ row.id }}"></div>
  </template>

  <template data-tm-col="assigned_topic_title" data-tm-label="Đề tài phân bổ" data-tm-sortable>
    <div class="{{ value ? '' : 'hidden' }}">
      <div class="font-medium">{{ value }}</div>
      <div class="text-sm">{{ row.assigned_teacher_name }}</div>
    </div>
    <div class="{{ value ? 'hidden' : '' }}">
      <span class="badge" data-variant="destructive">Chưa phân bổ</span>
    </div>
  </template>

  <template data-tm-col="_actions" data-tm-label="Thao tác" data-tm-align="right">
    <button type="button" class="btn" data-variant="outline" data-size="md"
      onclick="openManualAssignModal('{{ row.id }}')">
      <i class="fa-solid fa-pen-to-square"></i> Gán thủ công
    </button>
  </template>

  <script data-tm-data="allocation_table" type="application/json">
    <?= json_encode($groups ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
  </script>
</div>

<!-- Auto Allocate Modal -->
<div class="modal" id="auto-allocate-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Duyệt tự động</h3>
  </div>
  <div class="modal__content">
    <p>Hệ thống sẽ tự động phân bổ đề tài dựa trên nguyện vọng và thời điểm chốt nguyện vọng.</p>
    <p>Lưu ý: Chỉ các nhóm ĐÃ CHỐT nguyện vọng mới được tham gia phân bổ.</p>
    <div class="alert" data-variant="warning">
      <i class="fa-solid fa-triangle-exclamation"></i> Thao tác này sẽ ghi đè các phân bổ cũ!
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
    <div class="modal__content p-2">
      <p>Upload file Excel danh sách sinh viên đủ điều kiện làm đồ án. Cột chứa MSSV phải nằm ở cột thứ 2 (cột B), dữ liệu bắt đầu từ dòng 2.</p>
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

<script>
  function openManualAssignModal(groupId) {
    document.getElementById('manual_group_id').value = groupId;
    ModalHandler.instance.open('#manual-assign-modal');
  }

  function openReplaceMemberModal(groupId, oldStudentId) {
    document.getElementById('replace_group_id').value = groupId;
    document.getElementById('replace_old_student_id').value = oldStudentId;
    ModalHandler.instance.open('#replace-member-modal');
  }

  // Handle action confirm modal
  let currentForm = null;

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-confirm-action');
    if (btn) {
      currentForm = btn.closest('form');
      const msg = btn.getAttribute('data-confirm-msg') || 'Bạn có chắc chắn muốn thực hiện thao tác này?';

      const confirmMsg = document.getElementById('action-confirm-msg');
      if (confirmMsg) confirmMsg.textContent = msg;

      const modal = document.getElementById('action-confirm-modal');
      if (window.Modal) {
        window.Modal.open(modal);
      } else {
        ModalHandler.instance.open('#action-confirm-modal');
      }
    }
  });

  const confirmBtn = document.getElementById('action-confirm-btn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', () => {
      if (currentForm) {
        currentForm.submit();
      }
    });
  }

  (() => {
    const root = document.getElementById('allocation_table');
    if (!root) return;

    root.addEventListener('tm:render', (e) => {
      const visibleRows = e.detail.visibleRows;

      root.querySelectorAll('.members-container').forEach(container => {
        if (container.dataset.rendered) return;

        const groupId = container.dataset.groupId;
        const groupData = visibleRows.find(r => String(r.id) === String(groupId));

        if (groupData && groupData.members) {
          container.innerHTML = '';

          groupData.members.forEach(m => {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'allocation-table__member flex flex-col';
            if (!m.is_eligible) {
              rowDiv.classList.add('allocation-table__member--ineligible');
            }

            const nameDiv = document.createElement('div');
            nameDiv.innerHTML = `<span>${m.full_name} (${m.student_code})</span>`;
            if (groupData.is_admin_approved_solo) {
              nameDiv.insertAdjacentHTML('beforeend', '<span class="badge ml-1" data-variant="secondary">Làm 1 mình</span>');
            } else if (m.is_leader) {
              nameDiv.insertAdjacentHTML('beforeend', '<span class="badge ml-1" data-variant="primary">Nhóm trưởng</span>');
            }
            rowDiv.appendChild(nameDiv);

            if (!m.is_eligible && m.phone) {
              const phoneDiv = document.createElement('div');
              phoneDiv.className = 'text-xs mt-1';
              phoneDiv.innerHTML = `<i class="text-xs fa-solid fa-phone mr-1"></i> ${m.phone}`;
              rowDiv.appendChild(phoneDiv);
            }

            container.appendChild(rowDiv);
          });

          container.dataset.rendered = 'true';
        }
      });

      root.querySelectorAll('.aspirations-container').forEach(container => {
        if (container.dataset.rendered) return;

        const groupId = container.dataset.groupId;
        const groupData = visibleRows.find(r => String(r.id) === String(groupId));

        if (groupData && groupData.aspirations && groupData.aspirations.length > 0) {
          container.innerHTML = '';
          let isLocked = !!groupData.aspirations[0].locked_at;

          if (!isLocked) {
            container.innerHTML += '<div><span class="badge" data-variant="warning"><i class="fa-solid fa-unlock mr-1"></i> Chưa chốt</span></div>';
          }

          groupData.aspirations.forEach(asp => {
            const rowDiv = document.createElement('div');
            rowDiv.style.marginBottom = '0.25rem';
            rowDiv.innerHTML = `<span class="badge" data-variant="outline-alt">NV${asp.priority}</span> ${asp.topic_title || 'Đề tài #' + asp.topic_id}`;
            container.appendChild(rowDiv);
          });
          container.dataset.rendered = 'true';
        } else if (groupData) {
          container.innerHTML = '<span class="text-muted italic">Chưa đăng ký</span>';
          container.dataset.rendered = 'true';
        }
      });
    });
  })();
</script>

<?php $layout->end() ?>