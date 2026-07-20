<?php

use App\Enums\ProjectBatchStatus;
use App\Enums\ProjectTopicStatus;
use App\Models\ProjectBatch;
use App\Core\AppTime;
?>

<?php $layout->start("head") ?>
<link rel="stylesheet" href="<?= url('/public/css/allocation.css') ?>">
<?php $layout->end() ?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Chi tiết đợt: <?= htmlspecialchars($batch['title']) ?>
</h2>
<p class="title-wrapper__description">Danh sách đề tài do bạn đề xuất trong đợt này</p>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('teacher/project_batches') ?>" data-variant="outline" data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<?php
$canPropose = false;
if (!empty($batch['topic_proposal_start']) && !empty($batch['topic_proposal_end'])) {
  $now = AppTime::now();
  $start = new \DateTime($batch['topic_proposal_start']);
  $end = new \DateTime($batch['topic_proposal_end']);
  if ($end->format('H:i:s') === '00:00:00') {
    $end->setTime(23, 59, 59);
  }
  if ($now >= $start && $now <= $end) {
    $canPropose = true;
  }
}

if ($canPropose):
?>
  <a href="<?= url("teacher/project_batches/{$batch['id']}/topics/create") ?>" class="btn" data-variant="primary"
    data-size="lg">
    <i class="fa-solid fa-plus"></i> Gửi đề tài
  </a>
<?php endif; ?>
<?php $layout->end() ?>

<?php $layout->start('content') ?>
<div class="card">
  <div class="card__header">
    <legend class="card__title field__legend">Thông tin đợt đồ án</legend>
  </div>
  <hr class="separator">
  <div class="card__content">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <p class="text-sm">Niên khóa</p>
        <p class="font-medium"><?= htmlspecialchars($batch['min_class_of']) ?> -
          <?= htmlspecialchars($batch['max_class_of']) ?>
        </p>
      </div>
      <div>
        <p class="text-sm">Trạng thái</p>
        <?php
        $batchModel = new ProjectBatch();
        $batchModel->status = $batch['status'] ?? 'draft';
        $batchModel->topic_proposal_start = $batch['topic_proposal_start'] ?? null;
        $batchModel->topic_proposal_end = $batch['topic_proposal_end'] ?? null;
        $batchModel->registration_start = $batch['registration_start'] ?? null;
        $batchModel->registration_end = $batch['registration_end'] ?? null;
        $batchModel->allocation_published_at = $batch['allocation_published_at'] ?? null;
        $effectiveStatus = $batchModel->getEffectivePhase();
        ?>
        <span class="badge" data-variant="<?= ProjectBatchStatus::getVariant($effectiveStatus) ?>">
          <?= ProjectBatchStatus::getLabel($effectiveStatus) ?>
        </span>
      </div>
      <div>
        <p class="text-sm">Thời gian đề xuất đề tài</p>
        <p class="font-medium">
          <?= !empty($batch['topic_proposal_start']) ? date('d/m/Y', strtotime($batch['topic_proposal_start'])) : 'Chưa thiết lập' ?>
          -
          <?= !empty($batch['topic_proposal_end']) ? date('d/m/Y', strtotime($batch['topic_proposal_end'])) : 'Chưa thiết lập' ?>
        </p>
      </div>
      <div>
        <p class="text-sm">Thời gian đăng ký đề tài</p>
        <p class="font-medium">
          <?= !empty($batch['registration_start']) ? date('d/m/Y', strtotime($batch['registration_start'])) : 'Chưa thiết lập' ?>
          -
          <?= !empty($batch['registration_end']) ? date('d/m/Y', strtotime($batch['registration_end'])) : 'Chưa thiết lập' ?>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="stats-grid assignment-stats">
  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Đề tài được duyệt</span>
      <span class="stats-card__value"><?= $topicStats['approved'] ?> / <?= $topicStats['total'] ?></span>
    </div>
  </div>
  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Tổng sức chứa đề tài</span>
      <span class="stats-card__value"><?= $topicStats['capacity'] ?> SV</span>
    </div>
  </div>
  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Nhóm đang hướng dẫn</span>
      <span class="stats-card__value"><?= $groupStats['total_groups'] ?></span>
    </div>
  </div>
  <div class="card stats-card <?= $groupStats['ineligible_students'] > 0 ? 'border-destructive' : '' ?>">
    <div class="card__header">
      <span class="stats-card__label">SV đang hướng dẫn / Chỉ tiêu</span>
      <span class="stats-card__value <?= $groupStats['ineligible_students'] > 0 ? 'text-destructive' : '' ?>">
        <?= $groupStats['total_students'] ?> / <?= $supervisorInfo['max_students'] ?>
      </span>
    </div>
    <?php if ($groupStats['ineligible_students'] > 0): ?>
      <div class="text-sm mt-2 font-medium" style="color: var(--destructive);">
        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Có <?= $groupStats['ineligible_students'] ?> SV không đủ điều kiện
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="tabs w-full" data-tabs data-tabs-id="teacher-project-batch" data-tabs-panel-active="topics">
  <div class="tabs__list overflow-x-auto mb-4" role="tablist" aria-label="Nội dung đợt đồ án">
    <button type="button" class="tabs__trigger" role="tab" aria-selected="true" data-tabs-trigger="topics"
      data-tabs-trigger-state="active">
      Đề tài đã cung cấp <span class="badge ml-2" data-variant="secondary"><?= count($topics) ?></span>
    </button>
    <button type="button" class="tabs__trigger" role="tab" aria-selected="false" data-tabs-trigger="groups"
      data-tabs-trigger-state="idle" tabindex="-1">
      Nhóm hướng dẫn <span class="badge ml-2" data-variant="secondary"><?= count($groups) ?></span>
    </button>
  </div>

  <div class="tabs__panel" role="tabpanel" data-tabs-panel="topics" data-tabs-panel-state="active">
    <div class="card shadow-sm">
      <div class="card__header">
        <h3 class="font-semibold">Danh sách đề tài</h3>
        <p class="card__description">Cột "Số SV đăng ký" thể hiện số lượng sinh viên đã chọn đề tài này làm nguyện vọng 1.</p>
      </div>

      <hr class="separator">
      <div class="card__content">
        <div class="tm-container" data-tm="topics_table" data-tm-mode="client" data-tm-searchable>

          <template data-tm-col="stt" data-tm-label="STT" data-tm-width="60px">
            <span class="text-sm font-medium">{{ row.stt }}</span>
          </template>

          <template data-tm-col="title" data-tm-label="Tên đề tài" data-tm-sortable data-tm-filter-type="text">
            <span class="font-medium">{{ value }}</span>
          </template>

          <template data-tm-col="max_students" data-tm-label="Số sinh viên tối đa" data-tm-width="80px"></template>

          <template data-tm-col="registered_students_nv1" data-tm-label="Số SV đăng ký" data-tm-width="120px" data-tm-sortable>
            <span class="font-medium">{{ row.registered_students_nv1 }}</span>
          </template>

          <template data-tm-col="status" data-tm-label="Trạng thái" data-tm-filter-type="select"
            data-tm-filter-options='<?= json_encode(ProjectTopicStatus::getOptions()) ?>'>
            <span class="badge" data-variant="{{ row.status_variant }}">
              {{ row.status_label }}
            </span>
          </template>

          <template data-tm-col="actions" data-tm-label="Thao tác" data-tm-width="120px">
            <div class="flex gap-2 justify-end">
              <a href="{{ row.pdf_url }}" target="_blank" class="btn btn--icon" data-variant="outline" data-size="md"
                aria-label="Xem PDF" title="Xem PDF">
                <i class="fa-solid fa-file-pdf"></i>
              </a>

              <a href="{{ row.edit_url }}" class="btn btn--icon" style="{{ row.action_class }}" data-size="md" data-variant="outline" aria-label="Sửa"
                title="Sửa">
                <i class="fa-solid fa-pencil"></i>
              </a>
              <form action="{{ row.delete_url }}" method="POST" class="inline-block" style="{{ row.action_class }}">
                <?= csrf_field() ?>
                <button type="button" class="btn btn--icon btn-confirm-action" data-variant="destructive" data-size="md" aria-label="Xóa"
                  title="Xóa" data-modal-trigger="#action-confirm-modal" data-confirm-msg="Xác nhận xóa đề tài: {{ row.title }}?">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>

            </div>
          </template>

          <template data-tm-pagination></template>
        </div>
      </div>
    </div>
  </div>

  <div class="tabs__panel" role="tabpanel" data-tabs-panel="groups" data-tabs-panel-state="idle" hidden>
    <div class="card shadow-sm">
      <div class="card__header">
        <div>
          <h3 class="font-semibold">Danh sách nhóm đang hướng dẫn</h3>
          <p class="text-sm mt-1">Chỉ hiển thị các nhóm đã được phân vào đề tài của bạn.</p>
        </div>
        <div class="card__action">
          <?php if ($isAllocationPublished): ?>
            <button type="button" class="btn" data-variant="primary" data-size="md"
              data-modal-trigger="#print-topic-groups-modal" <?= empty($groups) ? 'disabled' : '' ?>>
              <i class="fa-solid fa-layer-group"></i> In tất cả nhóm đề tài
            </button>
          <?php endif; ?>
        </div>
      </div>
      <hr class="separator">
      <div class="card__content">
        <?php if (!$isAllocationPublished): ?>
          <div class="py-4 text-center">
            <i class="fa-solid fa-hourglass-half text-3xl mb-3"></i>
            <p>Đợt đồ án đang trong giai đoạn xét duyệt.</p>
            <p>Kết quả phân công nhóm sẽ được hiển thị tại đây sau khi Khoa công bố.</p>
          </div>
        <?php else: ?>
          <div class="tm-container" data-tm="assigned_groups_table" data-tm-mode="client" data-tm-searchable
            data-tm-selectable="true" data-tm-id-key="id">
            <template data-tm-col="topic" data-tm-label="Đề tài" data-tm-sortable data-tm-filter-type="text">
              <div class="font-medium">{{ value }}</div>
            </template>
            <template data-tm-col="members" data-tm-label="Sinh viên">
              <div class="members-container text-sm flex flex-col gap-1" data-group-id="{{ row.id }}"></div>
            </template>
            <template data-tm-col="actions" data-tm-label="" data-tm-width="90px">
              <a href="{{ row.print_url }}" target="_blank" class="btn btn--icon" data-variant="outline" data-size="md"
                aria-label="In phiếu nhóm" title="In phiếu nhóm">
                <i class="fa-solid fa-print"></i>
              </a>
            </template>
            <template data-tm-pagination></template>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
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

<div class="modal" id="print-topic-groups-modal" tabindex="-1" data-state="closed"
  aria-labelledby="print-topic-groups-title"
  style="width: min(calc(100vw - 2rem), var(--container-lg)); max-width: var(--container-lg);">
  <div class="modal__header">
    <h3 class="modal__title" id="print-topic-groups-title">Chọn đề tài cần in</h3>
    <p class="modal__description">Phiếu của tất cả nhóm được phân vào đề tài đã chọn sẽ mở trong trang xem trước.</p>
  </div>
  <div class="grid gap-2 max-h-96 overflow-y-auto">
    <?php
    $groupTopics = [];
    foreach ($groups as $group) {
      $groupTopics[(int) $group['assigned_topic_id']] = $group['assigned_topic_title'];
    }
    asort($groupTopics, SORT_NATURAL | SORT_FLAG_CASE);
    ?>
    <?php foreach ($groupTopics as $topicId => $topicTitle): ?>
      <button type="button" class="btn print-topic-groups justify-start w-full" data-variant="outline" data-size="lg"
        data-topic-id="<?= $topicId ?>">
        <i class="fa-solid fa-layer-group shrink-0"></i>
        <span class="text-left flex-1 min-w-0">
          <?= htmlspecialchars($topicTitle) ?>
        </span>
      </button>
    <?php endforeach; ?>
  </div>
  <div class="modal__footer">
    <button type="button" class="btn" data-variant="outline" data-size="lg" data-modal-close>Hủy</button>
  </div>
  <button type="button" class="modal__close" data-modal-close aria-label="Đóng">
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<form id="registration-print-form" action="<?= url("teacher/project_batches/{$batch['id']}/registration-forms/preview") ?>" method="POST" target="_blank"
  class="hidden">
  <?= csrf_field() ?>
  <div id="registration-print-group-inputs"></div>
</form>

<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="topics_table">
  <?= json_encode([
    'rows' => array_map(function ($index, $topic) use ($batch) {
      $t = (object) $topic;
      return [
        'stt' => $index + 1,
        'id' => $t->id,
        'title' => $t->title,
        'description' => $t->description,
        'max_students' => $t->max_students,
        'registered_students_nv1' => $t->registered_students_nv1 ?? 0,
        'status' => $t->status,
        'status_label' => ProjectTopicStatus::getLabel($t->status),
        'status_variant' => ProjectTopicStatus::getVariant($t->status),
        'can_edit' => in_array($t->status, [ProjectTopicStatus::DRAFT, ProjectTopicStatus::REJECTED]),
        'action_class' => in_array($t->status, [ProjectTopicStatus::DRAFT, ProjectTopicStatus::REJECTED]) ? '' : 'display: none !important;',
        'edit_url' => url("teacher/project_batches/{$batch['id']}/topics/{$t->id}/edit"),
        'delete_url' => url("teacher/project_batches/{$batch['id']}/topics/{$t->id}/delete"),
        'pdf_url' => $t->pdf_file_path ? url("public/storage/" . $t->pdf_file_path) : null
      ];
    }, array_keys($topics), array_values($topics)),
    'total' => count($topics),
    'page' => 1,
    'limit' => count($topics) > 0 ? count($topics) : 15
  ]) ?>
</script>
<script type="application/json" data-tm-data="assigned_groups_table">
  <?= json_encode([
    'rows' => array_map(function ($group) use ($batch) {
      return [
        'id' => (int) $group['id'],
        'topic_id' => (int) $group['assigned_topic_id'],
        'topic' => $group['assigned_topic_title'],
        'is_admin_approved_solo' => (bool)$group['is_admin_approved_solo'],
        'members' => array_map(function ($member) {
          return [
            'is_leader' => (bool)$member['is_leader'],
            'is_confirmed' => (bool)$member['is_confirmed'],
            'is_eligible' => (bool)$member['is_eligible'],
            'full_name' => $member['full_name'],
            'student_code' => $member['student_code'],
            'classroom_name' => $member['classroom_name'] ?: 'Chưa có lớp',
            'phone' => $member['phone'] ?? null,
            'email' => $member['email'] ?? null,
          ];
        }, $group['members'] ?? []),
        'print_url' => url("teacher/project_batches/{$batch['id']}/groups/{$group['id']}/registration-form"),
        'actions' => '',
      ];
    }, $groups),
    'total' => count($groups),
    'page' => 1,
    'limit' => count($groups) > 0 ? count($groups) : 15,
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
<script>
  (() => {
    const tableRoot = document.querySelector('[data-tm="assigned_groups_table"]');
    if (tableRoot) {
      tableRoot.addEventListener('tm:render', (e) => {
        const visibleRows = e.detail.visibleRows;
        tableRoot.querySelectorAll('.members-container').forEach(container => {
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
              nameDiv.className = 'popover';
              let badgesHTML = '';
              if (groupData.is_admin_approved_solo) {
                badgesHTML += '<span class="badge ml-1" data-variant="secondary">Làm 1 mình</span>';
              } else if (m.is_leader) {
                badgesHTML += '<span class="badge ml-1" data-variant="primary">Nhóm trưởng</span>';
              } else if (!m.is_confirmed) {
                badgesHTML += '<span class="badge ml-1" data-variant="warning" title="Chưa xác nhận tham gia nhóm">Chưa xác nhận</span>';
              }
              nameDiv.innerHTML = `
                <div class="popover__trigger flex items-center">
                  <i class="fa-solid fa-circle-info mr-2 text-muted"></i>
                  <span class="font-medium">${m.full_name}</span>
                  ${badgesHTML}
                </div>
                <div class="popover__content" data-side="right" data-align="start">
                  <div class="text-sm font-semibold mb-2">Thông tin sinh viên</div>
                  <div class="text-sm space-y-2">
                    <div><i class="fa-solid fa-id-card w-4 text-center mr-1"></i> MSSV: <strong>${m.student_code}</strong></div>
                    <div><i class="fa-solid fa-graduation-cap w-4 text-center mr-1"></i> Lớp: <strong>${m.classroom_name}</strong></div>
                    <div><i class="fa-solid fa-phone w-4 text-center mr-1"></i> SĐT: <strong>${m.phone || "Chưa cập nhật"}</strong></div>
                    <div><i class="fa-solid fa-envelope w-4 text-center mr-1"></i> Email: <strong>${m.email || "Chưa cập nhật"}</strong></div>
                  </div>
                </div>
              `;
              rowDiv.appendChild(nameDiv);
              container.appendChild(rowDiv);
              if (typeof PopoverHandler !== 'undefined') {
                PopoverHandler.instance.register(nameDiv);
              }
            });
            container.dataset.rendered = 'true';
          }
        });
      });
    }
  })();

  window.addEventListener('DOMContentLoaded', () => {

    const rows = <?= json_encode(array_map(fn($group) => ['id' => (int) $group['id'], 'topic_id' => (int) $group['assigned_topic_id']], $groups)) ?>;
    const form = document.getElementById('registration-print-form');
    const inputs = document.getElementById('registration-print-group-inputs');

    const submitPrint = (ids) => {
      ids = [...new Set(ids.map(Number).filter(Boolean))];
      if (!ids.length) return;
      inputs.replaceChildren(...ids.map(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'group_ids[]';
        input.value = id;
        return input;
      }));
      form.submit();
    };

    document.addEventListener('click', (event) => {
      const topic = event.target.closest('.print-topic-groups');
      if (topic) {
        if (typeof ModalHandler !== 'undefined') ModalHandler.instance?.close('#print-topic-groups-modal');
        submitPrint(rows.filter(row => row.topic_id === Number(topic.dataset.topicId)).map(row => row.id));
      }
    });

    TableManager.registerBulkActions('assigned_groups_table', {
      countLabel: count => `Đã chọn: ${count}`,
      actions: [{
        id: 'print-selected-groups',
        label: 'In nhóm đã chọn',
        ariaLabel: 'In phiếu các nhóm đã chọn',
        tooltip: 'In phiếu các nhóm đã chọn',
        icon: 'fa-solid fa-print',
        variant: 'primary',
        onClick: ({
          selectedIds
        }) => submitPrint(selectedIds),
      }],
    });

    document.addEventListener("click", (e) => {
      const btn = e.target.closest(".btn-confirm-action");
      if (btn) {
        const msg = btn.getAttribute("data-confirm-msg") || "Bạn có chắc chắn muốn thực hiện thao tác này?";
        document.getElementById("action-confirm-msg").textContent = msg;

        const form = btn.closest("form");
        const confirmBtn = document.getElementById("action-confirm-btn");
        if (confirmBtn) {
          confirmBtn.onclick = () => {
            if (form) form.submit();
          };
        }
      }
    });
  });
</script>
<?php $layout->end() ?>
