<?php

use App\Enums\ProjectBatchStatus;
use App\Enums\ProjectTopicStatus;
use App\Models\ProjectBatch;
?>

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
  $now = new \DateTime();
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
<div class="card mb-6">
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
      </div>

      <hr class="separator">
      <div class="card__content">
        <div class="tm-container" data-tm="topics_table" data-tm-mode="client" data-tm-searchable>

          <template data-tm-col="title" data-tm-label="Tên đề tài" data-tm-sortable data-tm-filter-type="text">
            <span class="font-medium">{{ value }}</span>
          </template>

          <template data-tm-col="description" data-tm-label="Mô tả">
            <div class="font-medium">
              <div class="{{ !row.description ? 'hidden' : '' }} text-sm mt-1 line-clamp-2"
                style="white-space: pre-line;">{{ row.description }}</div>
            </div>
          </template>

          <template data-tm-col="max_students" data-tm-label="Số sinh viên tối đa" data-tm-width="80px"></template>

          <template data-tm-col="status" data-tm-label="Trạng thái" data-tm-filter-type="select"
            data-tm-filter-options='<?= json_encode(ProjectTopicStatus::getOptions()) ?>'>
            <span class="badge" data-variant="{{ row.status_variant }}">
              {{ row.status_label }}
            </span>
          </template>

          <template data-tm-col="actions" data-tm-label="" data-tm-width="120px">
            <div class="flex gap-2 justify-end">
              <a href="{{ row.pdf_url }}" target="_blank" class="btn btn--icon" data-variant="outline" data-size="md"
                aria-label="Xem PDF" title="Xem PDF">
                <i class="fa-solid fa-file-pdf"></i>
              </a>

              <a href="{{ row.edit_url }}" class="btn btn--icon" data-size="md" data-variant="outline" aria-label="Sửa"
                title="Sửa">
                <i class="fa-solid fa-pencil"></i>
              </a>
              <form action="{{ row.delete_url }}" method="POST" class="inline-block"
                onsubmit="return confirm('Bạn có chắc chắn muốn xóa đề tài này không?');">
                <button type="submit" class="btn btn--icon" data-variant="destructive" data-size="md" aria-label="Xóa"
                  title="Xóa">
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
              <div style="white-space: pre-line">{{ value }}</div>
            </template>
            <template data-tm-col="assigned_at" data-tm-label="Ngày phân công" data-tm-sortable></template>
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
    'rows' => array_map(function ($topic) use ($batch) {
    $t = (object) $topic;
    return [
      'id' => $t->id,
      'title' => $t->title,
      'description' => $t->description,
      'max_students' => $t->max_students,
      'status' => $t->status,
      'status_label' => ProjectTopicStatus::getLabel($t->status),
      'status_variant' => ProjectTopicStatus::getVariant($t->status),
      'can_edit' => in_array($t->status, [ProjectTopicStatus::DRAFT, ProjectTopicStatus::REJECTED]),
      'edit_url' => url("teacher/project_batches/{$batch['id']}/topics/{$t->id}/edit"),
      'delete_url' => url("teacher/project_batches/{$batch['id']}/topics/{$t->id}/delete"),
      'pdf_url' => $t->pdf_file_path ? url("storage/" . $t->pdf_file_path) : null
    ];
  }, $topics),
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
        'members' => implode("\n", array_map(function ($member) {
        return ($member['is_leader'] ? 'Nhóm trưởng: ' : '') . $member['full_name'] . ' · ' . $member['student_code'] . ' · ' . ($member['classroom_name'] ?: 'Chưa có lớp');
      }, $group['members'] ?? [])),
        'assigned_at' => !empty($group['assigned_at']) ? date('d/m/Y H:i', strtotime($group['assigned_at'])) : '—',
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
  })();
</script>
<?php $layout->end() ?>