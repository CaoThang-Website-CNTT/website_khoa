<?php

/**
 * View: Đăng ký đề tài (dành cho sinh viên)
 * Route: /student/project_batches/{id}/topics
 */
$student = $student ?? null;
$currentBatch = $currentBatch ?? null;
$group = $group ?? null;
$isLeader = $isLeader ?? false;
$isConfirmed = $isConfirmed ?? false;
$topics = $topics ?? [];
$pagination = $pagination ?? null;
$aspirations = $aspirations ?? [];
$aspirationTopicIds = $aspirationTopicIds ?? [];
$maxAspirations = $maxAspirations ?? 3;

$isLocked = $isLocked ?? false;
$phase = $phase ?? 'upcoming';
$isAllocationPublished = $isAllocationPublished ?? false;
$canInteract = ($phase === 'registration' && !$isAllocationPublished);

$canAddAspiration = $canInteract && $isLeader && count($aspirationTopicIds) < $maxAspirations;

$index = 1;
$topicsDataMapped = array_map(function ($topic) use ($aspirationTopicIds, $isLeader, $canAddAspiration, $currentBatch, $isLocked, &$index, $canInteract) {
  $isAdded = in_array($topic['id'], $aspirationTopicIds);
  return [
    'stt' => $index++,
    'id' => $topic['id'],
    'title' => $topic['title'],
    'description' => $topic['description'],
    'teacher_name' => $topic['teacher_name'],
    'pdf_file_url' => !empty($topic['pdf_file_path']) ? url('/storage/' . ltrim($topic['pdf_file_path'], '/')) : null,
    'is_added' => $isAdded,
    'is_leader' => (bool)$isLeader,
    'can_add' => !$isAdded && $isLeader && $canAddAspiration && !$isLocked && $canInteract,
    'add_url' => url("student/project_batches/{$currentBatch['id']}/aspirations/add"),
    'is_locked' => $isLocked,
    'registered_count' => (int)($topic['registered_students_count'] ?? 0),
    'max_students' => (int)($topic['max_students'] ?? 0)
  ];
}, $topics);
?>

<?php $layout->start("heading") ?>
<div>
  <h2 class="title-wrapper__title">Danh sách đề tài</h2>
  <p class="title-wrapper__description">Chọn đề tài và thêm vào danh sách nguyện vọng (Tối đa <?= $maxAspirations ?> đề tài).</p>
  <div class="alert mt-2" data-variant="info">
    <i class="fa-solid fa-circle-info mr-2"></i> Lưu ý: Nếu nhóm trượt tất cả nguyện vọng đã đăng ký, hệ thống sẽ phân bổ ngẫu nhiên vào các đề tài còn trống.
  </div>
</div>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url("student/project_batches/{$currentBatch['id']}") ?>" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
  Quay lại
</a>
<?php $layout->end() ?>

<?php $layout->start("content") ?>

<div class="detail-layout">
  <div class="detail-layout__main flex-1">
    <div class="card shadow">
      <div class="card__content">
        <div class="flex justify-between items-center mb-4">
          <div>
            <h3 class="text-lg font-semibold">Danh sách các đề tài Đồ án tốt nghiệp</h3>
            <?php if ($group): ?>
              <p class="text-sm">Bạn đã đăng ký <?= count($aspirationTopicIds) ?>/<?= $maxAspirations ?> nguyện vọng.</p>
            <?php endif; ?>
            <p class="text-sm mt-2" style="color: var(--muted-foreground);">* Cột "Đã đăng ký" đếm số lượng sinh viên của các nhóm chọn đề tài làm Nguyện vọng 1 và Đã chốt nguyện vọng.</p>
          </div>
        </div>

        <?php if (!$group): ?>
          <div class="alert mb-4" data-variant="warning">
            <i class="fa-solid fa-list-check"></i>
            <div class="alert-content">
              <p class="alert-description">Bạn cần tham gia nhóm trước khi đăng ký nguyện vọng. Hãy quay lại trang chi tiết đợt để tạo hoặc gia nhập nhóm.</p>
            </div>
          </div>
        <?php elseif ($isLocked): ?>
          <div class="alert mb-4" data-variant="success">
            <i class="fa-solid fa-lock"></i>
            <div class="alert-content">
              <p class="alert-description">Nguyện vọng đã được chốt, không thể thêm mới.</p>
            </div>
          </div>
        <?php elseif (!$canInteract): ?>
          <div class="alert mb-4" data-variant="warning">
            <i class="fa-solid fa-clock"></i>
            <div class="alert-content">
              <p class="alert-description">Đã hết thời gian đăng ký và chỉnh sửa nguyện vọng.</p>
            </div>
          </div>
        <?php elseif (!$isLeader): ?>
          <div class="alert mb-4" data-variant="warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div class="alert-content">
              <p class="alert-description">Chỉ nhóm trưởng mới có quyền đăng ký nguyện vọng.</p>
            </div>
          </div>
        <?php elseif (!$isConfirmed): ?>
          <div class="alert mb-4" data-variant="warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div class="alert-content">
              <p class="alert-description">Nhóm của bạn chưa được thành lập thành công. Hãy chờ thành viên kia xác nhận.</p>
            </div>
          </div>
        <?php endif; ?>

        <div class="tm-container" data-tm="student_topics_table" data-tm-mode="client" data-tm-searchable data-tm-id-key="id">
          <template data-tm-col="stt" data-tm-label="STT" data-tm-width="5%">
            <div class="text-center">{{ row.stt }}</div>
          </template>

          <template data-tm-col="title" data-tm-label="Tên đề tài" data-tm-filter-type="text">
            <div class="font-medium line-clamp-2" title="{{ row.title }}">
              {{ row.title }}
            </div>
          </template>

          <template data-tm-col="teacher_name" data-tm-label="Giảng viên" data-tm-width="25%" data-tm-filter-type="text" data-tm-sortable>
            <span>{{ row.teacher_name }}</span>
          </template>

          <template data-tm-col="registered" data-tm-label="Đã đăng ký / Tối đa" data-tm-width="15%">
            <div class="text-center">
              <span class="badge" data-variant="secondary">
                {{ row.registered_count }} / {{ row.max_students }} SV
              </span>
            </div>
          </template>

          <template data-tm-col="details" data-tm-label="Chi tiết">
            <div>
              <a href="{{ row.pdf_file_url }}" target="_blank" class="{{ !row.pdf_file_url ? 'hidden' : '' }} btn" data-size="sm" data-variant="outline">
                <i class="fa-solid fa-file-pdf"></i> Xem
              </a>
              <span class="{{ row.pdf_file_url ? 'hidden' : '' }}">Không có file</span>
            </div>
          </template>

          <template data-tm-col="actions" data-tm-label="Thao tác">
            <div class="<?= !$group ? 'hidden' : '' ?>">
              <span class="badge {{ !row.is_added ? 'hidden' : '' }}" data-variant="success">Đã thêm NV</span>

              <div class="{{ row.is_added ? 'hidden' : '' }}">
                <div class="{{ !row.is_leader ? 'hidden' : '' }}">
                  <form action="{{ row.add_url }}" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="topic_id" value="{{ row.id }}">
                    <button type="submit" class="btn" data-variant="primary" data-size="sm" {{ !row.can_add ? 'disabled title="Đã đủ nguyện vọng tối đa hoặc đã chốt"' : '' }}>
                      <i class="fa-solid fa-plus"></i>Thêm
                    </button>
                  </form>
                </div>

                <div class="{{ row.is_leader ? 'hidden' : '' }}">
                  <button class="btn" data-variant="secondary" data-size="sm" disabled>Thêm</button>
                </div>
              </div>
            </div>
            <div class="<?= $group ? 'hidden' : '' ?>">
              <span class="text-sm" style="color: var(--muted-foreground)">Cần tạo nhóm</span>
            </div>
          </template>

          <template data-tm-pagination></template>
        </div>
      </div>
    </div>
  </div>

  <div class="detail-layout__sidebar">
    <div class="card shadow">
      <div class="card__header">
        <h3 class="text-lg font-semibold">Nguyện vọng của nhóm</h3>
      </div>
      <hr class="separator">
      <div class="card__content">
        <?php if ($isLocked): ?>
          <div class="alert mb-4 flex justify-between items-center" data-variant="success">
            <div>
              <i class="fa-solid fa-lock mr-2"></i> Đã chốt
            </div>
            <?php if ($canInteract && $isLeader): ?>
              <form action="<?= url("student/project_batches/{$currentBatch['id']}/aspirations/unlock") ?>" method="POST" class="inline-block shrink-0">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_to" value="topics">
                <button type="button" class="btn btn-confirm-action" data-variant="destructive" data-size="sm" data-confirm-msg="CẢNH BÁO: Mở khóa sẽ làm mất lợi thế thời gian. Bạn có chắc chắn muốn mở khóa?" data-modal-trigger="#action-confirm-modal">
                  <i class="fa-solid fa-unlock"></i>
                </button>
              </form>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if (!$group): ?>
          <div class="py-8 text-center">
            <i class="fa-solid fa-users text-3xl mb-3"></i>
            <p>Chưa tham gia nhóm.</p>
          </div>
        <?php elseif (empty($aspirations)): ?>
          <div class="py-8 text-center">
            <i class="fa-solid fa-file-circle-question text-3xl mb-3"></i>
            <p>Chưa đăng ký nguyện vọng.</p>
          </div>
        <?php else: ?>
          <form id="form-reorder-aspirations" action="<?= url("student/project_batches/{$currentBatch['id']}/aspirations/reorder") ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="topics">
            <div id="aspirations-list" class="space-y-3">
              <?php foreach ($aspirations as $index => $aspiration): ?>
                <div class="flex justify-between items-center p-3 border rounded-md drag-item" style="background-color: var(--card);" data-id="<?= $aspiration['topic_id'] ?>">
                  <div class="flex items-center flex-1">
                    <?php if ($canInteract && $isLeader && !$isLocked && count($aspirations) > 1): ?>
                      <i class="fa-solid fa-grip-vertical cursor-grab px-3 mr-3 handle" style="color: var(--muted-foreground);"></i>
                    <?php endif; ?>
                    <div>
                      <div class="font-medium aspiration-title line-clamp-1" title=<?= htmlspecialchars($aspiration['topic_title']) ?>>
                        <span class="mr-2 font-bold aspiration-index">#<?= $index + 1 ?></span>
                        <?= htmlspecialchars($aspiration['topic_title']) ?>
                      </div>
                      <div class="text-xs mt-1" style="color: var(--muted-foreground);">
                        GV: <?= htmlspecialchars($aspiration['teacher_name']) ?>
                      </div>
                    </div>
                    <input type="hidden" name="topic_ids[]" value="<?= $aspiration['topic_id'] ?>">
                  </div>
                  <?php if ($canInteract && $isLeader && !$isLocked): ?>
                    <div class="ml-2">
                      <button type="button" class="btn btn-remove-aspiration" data-variant="destructive" data-size="sm" title="Xóa" data-topic-id="<?= $aspiration['topic_id'] ?>" data-topic-title="<?= htmlspecialchars($aspiration['topic_title']) ?>">
                        <i class="fa-solid fa-trash-can"></i>
                      </button>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
            <?php if ($canInteract && $isLeader && count($aspirations) > 1 && !$isLocked): ?>
              <div class="mt-4 flex flex-col items-center gap-3">
                <button type="submit" class="btn hidden shrink-0 w-full" id="btn-save-order" data-variant="primary" data-size="sm">Lưu thứ tự mới</button>
              </div>
            <?php endif; ?>
          </form>

          <?php if ($canInteract && $isLeader && !$isLocked): ?>
            <form id="form-remove-aspiration" action="<?= url("student/project_batches/{$currentBatch['id']}/aspirations/remove") ?>" method="POST" class="hidden">
              <?= csrf_field() ?>
              <input type="hidden" name="redirect_to" value="topics">
              <input type="hidden" name="topic_id" id="remove_topic_id" value="">
            </form>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <?php if ($group && $canInteract && $isLeader && !empty($aspirations) && !$isLocked): ?>
        <hr class="separator">
        <div class="card__footer">
          <form action="<?= url("student/project_batches/{$currentBatch['id']}/aspirations/lock") ?>" class="w-full" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="topics">
            <button type="button" class="btn btn-confirm-action w-full" data-variant="primary" data-size="md" data-confirm-msg="Sau khi chốt, bạn KHÔNG THỂ thay đổi thứ tự hoặc xóa nguyện vọng. Chốt nguyện vọng càng sớm càng có lợi khi xét duyệt nguyện vọng. Bạn có chắc chắn muốn chốt?" data-modal-trigger="#action-confirm-modal">
              <i class="fa-solid fa-lock mr-2"></i> Chốt nguyện vọng
            </button>
          </form>
        </div>
      <?php endif; ?>
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

<script type="application/json" data-tm-data="student_topics_table">
  <?= json_encode($topicsDataMapped) ?>
</script>

<?php $layout->end() ?>

<?php $layout->start("scripts") ?>
<script>
  let currentForm = null;

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-confirm-action');
    if (btn) {
      currentForm = btn.closest('form');
      const msg = btn.getAttribute('data-confirm-msg') || 'Bạn có chắc chắn muốn thực hiện thao tác này?';

      const confirmMsg = document.getElementById('action-confirm-msg');
      if (confirmMsg) confirmMsg.textContent = msg;
    }

    // Nút xóa nguyện vọng lẻ
    const removeBtn = e.target.closest('.btn-remove-aspiration');
    if (removeBtn) {
      const topicId = removeBtn.getAttribute('data-topic-id');
      const topicTitle = removeBtn.getAttribute('data-topic-title');
      document.getElementById('remove_topic_id').value = topicId;
      currentForm = document.getElementById('form-remove-aspiration');

      const confirmMsg = document.getElementById('action-confirm-msg');
      if (confirmMsg) confirmMsg.textContent = `Bạn có chắc chắn muốn xóa nguyện vọng "${topicTitle}"?`;

      const modal = document.getElementById('action-confirm-modal');
      if (typeof ModalHandler !== 'undefined') {
        ModalHandler.instance.open('#action-confirm-modal');
      } else {
        modal.setAttribute('data-state', 'open');
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

  document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('aspirations-list');
    const saveBtn = document.getElementById('btn-save-order');

    if (list && saveBtn && window.DnD) {
      DnD.create(list, {
        handle: '.handle',
        animation: 150,
        ghostClass: 'opacity-50',
        onEnd: function() {
          saveBtn.classList.remove('hidden');
          const items = list.querySelectorAll('.drag-item');
          items.forEach((item, index) => {
            const idxSpan = item.querySelector('.aspiration-index');
            if (idxSpan) {
              idxSpan.textContent = '#' + (index + 1);
            }
          });
        }
      });
    }
  });
</script>
<?php $layout->end() ?>