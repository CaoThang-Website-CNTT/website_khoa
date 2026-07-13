<?php

use App\Enums\ProjectTopicStatus;

$batchObj = (object) $batch;
$topicObj = (object) $topic;
$statusLabel = ProjectTopicStatus::getLabel($topicObj->status);
$statusVariant = ProjectTopicStatus::getVariant($topicObj->status);
?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Chi tiết đề tài</h2>
<p class="title-wrapper__description">Đợt: <?= htmlspecialchars($batchObj->title) ?></p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("admin/project_batches/{$batchObj->id}/topics") ?>" class="btn" data-variant="outline" data-size="lg">
  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
  Quay lại
</a>
<?php $layout->end() ?>

<?php $layout->start('content') ?>

<?php if ($topicObj->status === ProjectTopicStatus::APPROVED): ?>
  <div class="stats-grid assignment-stats">
    <div class="card stats-card">
      <div class="card__header">
        <span class="stats-card__label">Nhóm chọn Nguyện vọng 1</span>
        <span class="stats-card__value"><?= (int)($topicObj->groups_nv1_count ?? 0) ?></span>
      </div>
    </div>
    <div class="card stats-card">
      <div class="card__header">
        <span class="stats-card__label">Tổng nhóm đăng ký (ở tất cả nguyện vọng)</span>
        <span class="stats-card__value"><?= (int)($topicObj->groups_all_nv_count ?? 0) ?></span>
      </div>
    </div>
    <div class="card stats-card">
      <div class="card__header">
        <span class="stats-card__label">Số Sinh viên tối đa</span>
        <span class="stats-card__value"><?= (int)$topicObj->max_students ?></span>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="detail-layout">
  <div class="detail-layout__main">
    <div class="card shadow">
      <div class="card__header">
        <h3 class="card__title">Thông tin chung</h3>
      </div>
      <hr class="separator">
      <div class="card__content space-y-4">
        <div>
          <h4 class="text-sm font-medium">Tên đề tài</h4>
          <p class="mt-1 font-medium text-xl"><?= htmlspecialchars($topicObj->title) ?></p>
        </div>
        <div>
          <h4 class="text-sm font-medium">Mô tả</h4>
          <p class="mt-1"><?= nl2br(htmlspecialchars($topicObj->description ?: 'Không có mô tả')) ?></p>
        </div>
        <?php if (!empty($topicObj->pdf_file_path)): ?>
          <a href="<?= url('/storage/' . ltrim($topicObj->pdf_file_path, '/')) ?>" target="_blank" class="btn" data-size="md" data-variant="secondary">
            <i class="fa-solid fa-file-pdf"></i>
            <span>Xem chi tiết đề tài</span>
          </a>
        <?php else: ?>
          <div class="text-sm">Chưa có file đính kèm</div>
        <?php endif; ?>
        <div>
          <h4 class="text-sm font-medium">Số sinh viên tối đa</h4>
          <p class="mt-1"><?= (int)$topicObj->max_students ?> sinh viên</p>
        </div>
      </div>
    </div>
  </div>

  <div class="detail-layout__sidebar">
    <div class="card shadow">
      <div class="card__header">
        <h3 class="card__title">Giảng viên hướng dẫn</h3>
      </div>
      <hr class="separator">
      <div class="card__content space-y-3">
        <p class="font-medium"><?= htmlspecialchars($topicObj->teacher_name) ?></p>
        <p class="text-sm text-muted-foreground"><?= htmlspecialchars($topicObj->teacher_email ?? 'Chưa cập nhật email') ?></p>

        <?php if (!empty($topicObj->teacher_phone)): ?>
          <p class="text-sm">
            <i class="fa-solid fa-phone mr-2"></i>
            <span><?= htmlspecialchars($topicObj->teacher_phone) ?></span>
          </p>
        <?php endif; ?>
      </div>
    </div>


    <div class="card shadow">
      <div class="card__header">
        <h3 class="card__title">Trạng thái duyệt</h3>
      </div>
      <hr class="separator">
      <div class="card__content space-y-4">
        <div>
          <span class="badge" data-variant="<?= $statusVariant ?>" data-size="lg"><?= $statusLabel ?></span>
        </div>

        <?php if ($topicObj->status === ProjectTopicStatus::REJECTED && !empty($topicObj->reject_reason)): ?>
          <div>
            <p class="font-semibold mb-1">Lý do từ chối:</p>
            <p><?= nl2br(htmlspecialchars($topicObj->reject_reason)) ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
</div>
<?php $layout->end() ?>