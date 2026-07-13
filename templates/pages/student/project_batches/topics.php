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
$aspirationTopicIds = $aspirationTopicIds ?? [];
$maxAspirations = $maxAspirations ?? 3;

$isLocked = $isLocked ?? false;
$phase = $phase ?? 'upcoming';
$isAllocationPublished = $isAllocationPublished ?? false;
$canInteract = ($phase === 'registration' && !$isAllocationPublished);

$canAddAspiration = $canInteract && $isLeader && count($aspirationTopicIds) < $maxAspirations;

$topicsDataMapped = array_map(function ($topic) use ($aspirationTopicIds, $isLeader, $canAddAspiration, $currentBatch, $isLocked) {
  $isAdded = in_array($topic['id'], $aspirationTopicIds);
  return [
    'id' => $topic['id'],
    'title' => $topic['title'],
    'description' => $topic['description'],
    'teacher_name' => $topic['teacher_name'],
    'pdf_file_url' => !empty($topic['pdf_file_path']) ? url('/public/media/' . $topic['pdf_file_path']) : null,
    'is_added' => $isAdded,
    'is_leader' => (bool)$isLeader,
    'can_add' => !$isAdded && $isLeader && $canAddAspiration && !$isLocked && $canInteract,
    'add_url' => url("student/project_batches/{$currentBatch['id']}/aspirations/add"),
    'is_locked' => $isLocked
  ];
}, $topics);
?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">Danh sách đề tài</h2>
<p class="title-wrapper__description">Chọn đề tài và thêm vào danh sách nguyện vọng (Tối đa <?= $maxAspirations ?> đề tài).</p>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url("student/project_batches/{$currentBatch['id']}") ?>" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
  Quay lại
</a>
<?php $layout->end() ?>

<?php $layout->start("content") ?>

<div class="card shadow">
  <div class="card__content">

    <div class="flex justify-between items-center mb-4">
      <div>
        <h3 class="text-lg font-semibold">Danh sách các đề tài đã duyệt</h3>
        <p class="text-sm">Bạn đã đăng ký <?= count($aspirationTopicIds) ?>/<?= $maxAspirations ?> nguyện vọng.</p>
      </div>
    </div>

    <?php if ($isLocked): ?>
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
      <template data-tm-col="id" data-tm-label="STT" data-tm-width="5%">
        <span class="text-center">{{ __index + 1 }}</span>
      </template>

      <template data-tm-col="title" data-tm-label="Tên đề tài" data-tm-filter-type="text">
        <div class="font-medium">
          {{ row.title }}
        </div>
      </template>

      <template data-tm-col="description" data-tm-label="Mô tả">
        <div class="font-medium">
          <div class="{{ !row.description ? 'hidden' : '' }} text-sm mt-1 line-clamp-2" style="white-space: pre-line;" title="{{ row.description }}">{{ row.description }}</div>
        </div>
      </template>

      <template data-tm-col="teacher_name" data-tm-label="Giảng viên" data-tm-width="25%" data-tm-filter-type="text" data-tm-sortable>
        <span>{{ row.teacher_name }}</span>
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
        <div>
          <span class="badge {{ !row.is_added ? 'hidden' : '' }}" data-variant="success">Đã thêm NV</span>

          <div class="{{ row.is_added ? 'hidden' : '' }}">
            <div class="{{ !row.is_leader ? 'hidden' : '' }}">
              <form action="{{ row.add_url }}" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="topic_id" value="{{ row.id }}">
                <button type="submit" class="btn" data-variant="primary" data-size="sm" {{ !row.can_add ? 'disabled title="Đã đủ nguyện vọng tối đa hoặc đã chốt"' : '' }}>
                  <i class="fa-solid fa-plus"></i>Thêm nguyện vọng
                </button>
              </form>
            </div>

            <div class="{{ row.is_leader ? 'hidden' : '' }}">
              <button class="btn" data-variant="secondary" data-size="sm" disabled>Thêm NV</button>
            </div>
          </div>
        </div>
      </template>

      <template data-tm-pagination></template>
    </div>
  </div>
</div>

<script type="application/json" data-tm-data="student_topics_table">
  <?= json_encode($topicsDataMapped) ?>
</script>

<?php $layout->end() ?>