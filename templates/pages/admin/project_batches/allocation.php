<?php

use App\Enums\ProjectBatchStatus;

$batchObj = (object) $batchObj;
?>

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
<button type="button" class="btn" data-variant="primary" data-size="lg" onclick="ModalHandler.instance.open('#auto-allocate-modal')">
  <i class="fa-solid fa-wand-magic-sparkles"></i>
  Phân bổ tự động
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
      Gán thủ công
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
    <p>Lưu ý: Chỉ các nhóm ĐÃ CHỐT nguyện vọng mới được tham gia phân bổ. Những nhóm còn lại sẽ rơi vào trạng thái "Chưa có đề tài".</p>
    <p style="color: var(--toast-warning-color)"><i class="fa-solid fa-triangle-exclamation"></i> Thao tác này sẽ ghi đè các phân bổ cũ!</p>
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
      <button type="submit" class="btn" data-variant="primary" data-size="lg">Xác nhận gán</button>
    </div>
  </form>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<script>
  function openManualAssignModal(groupId) {
    document.getElementById('manual_group_id').value = groupId;
    ModalHandler.instance.open('#manual-assign-modal');
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
            rowDiv.style.marginBottom = '0.25rem';

            const textSpan = document.createElement('span');
            textSpan.textContent = `• ${m.full_name} (${m.student_code}) `;
            rowDiv.appendChild(textSpan);

            if (m.is_leader) {
              rowDiv.insertAdjacentHTML('beforeend', '<span class="badge" data-variant="primary">Nhóm trưởng</span> ');
            }

            if (!m.is_eligible) {
              rowDiv.insertAdjacentHTML('beforeend', '<span class="badge" data-variant="destructive">Cấm</span>');
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