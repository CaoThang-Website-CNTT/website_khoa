<?php

/**
 * View: Chấm điểm thực tập (Teacher)
 * Route: /teacher/internship_batches/{batchId}/grade/{batchStudentId}
 */

$batchId = $batchId ?? null;
$batchStudentId = $batchStudentId ?? null;
$data = $data ?? [];
$canGrade = $canGrade ?? false;

$student = $data['student'] ?? null;
$submissions = $data['submissions'] ?? [];
$allSubmissions = $data['all_submissions'] ?? [];
$grade = $data['grade'] ?? null;
$deadline = $data['deadline'] ?? null;

$historyByType = [];
foreach ($allSubmissions as $sub) {
  $type = $sub['type'] ?? 'internship_report';
  if (!isset($historyByType[$type])) {
    $historyByType[$type] = [];
  }
  $historyByType[$type][] = $sub;
}

$companyName = $student['company_name'] ?? 'Chưa có thông tin';
?>
<link rel="stylesheet" href="<?= url('public/css/teacher_grading.css') ?>">

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6">
      <h2 class="title text-2xl font-semibold">
        Chấm điểm: <?= htmlspecialchars($student['full_name'] ?? '') ?>
      </h2>
      <div class="text-sm flex items-center" style="color: var(--muted-foreground)">
        MSSV: <?= htmlspecialchars($student['student_code'] ?? '--') ?> - Công ty: <?= htmlspecialchars($companyName) ?>
      </div>
    </div>

    <div class="flex gap-2">
      <a href="<?= url("teacher/internship_batches/{$batchId}") ?>" data-variant="outline" data-size="md" class="btn">
        <i class="fa-solid fa-chevron-left"></i>
        Quay lại
      </a>

      <button type="button" class="btn js-sidebar-toggle" data-variant="outline" data-size="md" title="Thu gọn/Mở rộng">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<div class="detail-layout detail-layout--collapsible">
  <!-- CỘT CHÍNH (TRÁI) -->
  <div class="detail-layout__main">
    <div class="viewer-tabs">
      <?php if (empty($submissions)): ?>
        <button class="tab-btn active">Không có tài liệu</button>
      <?php else: ?>
        <?php
        $typeLabels = [
          'internship_report' => 'Báo cáo TT',
          'evaluation_form' => 'Phiếu đánh giá',
          'company_survey' => 'Khảo sát DN',
          'related_photo' => 'Ảnh khác'
        ];
        $isFirst = true;
        foreach ($submissions as $sub):
          $type = $sub['type'] ?? 'internship_report';
        ?>
          <button class="tab-btn <?= $isFirst ? 'active' : '' ?>" data-target="viewer-<?= $sub['id'] ?>">
            <?= $typeLabels[$type] ?? 'Tài liệu' ?>
          </button>
        <?php
          $isFirst = false;
        endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="viewer-content">
      <?php if (empty($submissions)): ?>
        <div class="empty-state h-full flex flex-col items-center justify-center" style="color: var(--muted-foreground);">
          <i class="fa-solid fa-file-circle-xmark text-4xl mb-4"></i>
          <p>Sinh viên chưa nộp bất kỳ tài liệu nào.</p>
        </div>
      <?php else: ?>
        <?php
        $isFirst = true;
        foreach ($submissions as $sub):
          $type = $sub['type'] ?? 'internship_report';
          $history = $historyByType[$type] ?? [];
        ?>
          <div id="viewer-<?= $sub['id'] ?>" class="viewer-pane <?= $isFirst ? 'active' : '' ?>">
            <?php if ($type === 'related_photo'): ?>
              <!-- Hiển thị toàn bộ ảnh -->
              <div class="image-gallery p-4 h-full">
                <?php foreach ($history as $imgSub):
                  $imgUrl = url("api/v1/teacher/submissions/{$imgSub['id']}/preview");
                ?>
                  <div class="image-gallery__item mb-6">
                    <div class="mb-2 text-sm font-medium" style="color: var(--muted-foreground);">
                      <i class="fa-regular fa-clock mr-1"></i> <?= date('d/m/Y H:i', strtotime($imgSub['submitted_at'])) ?>
                      <span class="badge" data-variant="secondary"><?= htmlspecialchars($imgSub['original_file_name']) ?></span>
                    </div>
                    <img src="<?= $imgUrl ?>" class="js-lightbox-trigger image-gallery__img shadow-sm border" alt="<?= htmlspecialchars($imgSub['original_file_name']) ?>">
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <?php if (count($history) > 1): ?>
                <div class="version-selector p-2 flex items-center justify-between">
                  <span class="text-sm font-medium" style="color: var(--muted-foreground)"><i class="fa-solid fa-clock-rotate-left mr-1"></i> Lịch sử nộp:</span>
                  <select class="field__input js-version-select" data-target="iframe-<?= $sub['id'] ?>">
                    <?php foreach ($history as $index => $hSub):
                      $hUrl = url("api/v1/teacher/submissions/{$hSub['id']}/preview");
                    ?>
                      <option value="<?= $hUrl ?>">
                        <?= date('d/m/Y H:i', strtotime($hSub['submitted_at'])) ?> <?= $index === 0 ? '(Bản mới nhất)' : '' ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>
              <iframe id="iframe-<?= $sub['id'] ?>" src="<?= url("api/v1/teacher/submissions/{$sub['id']}/preview") ?>" class="pdf-viewer" frameborder="0" style="<?= count($history) > 1 ? 'height: calc(100% - 45px);' : 'height: 100%;' ?>"></iframe>
            <?php endif; ?>
          </div>
        <?php
          $isFirst = false;
        endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- SIDEBAR -->
  <div class="detail-layout__sidebar">
    <div class="card shadow h-full flex flex-col">
      <div class="card__header">
        <h3 class="card__title">Đánh giá</h3>
        <?php if (!$canGrade): ?>
          <div class="mt-2 text-sm text-destructive"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Đã hết hạn chấm điểm.</div>
        <?php elseif ($deadline): ?>
          <div class="mt-2 text-sm text-warning"><i class="fa-solid fa-clock mr-1"></i> Hạn chót: <?= date('d/m/Y', strtotime($deadline)) ?></div>
        <?php endif; ?>
      </div>
      <hr class="separator" />

      <div class="card__content flex-1">
        <form action="<?= url("teacher/internship_batches/{$batchId}/grade/{$batchStudentId}") ?>" method="POST" id="gradingForm">
          <?= csrf_field() ?>

          <div class="field mb-4" data-field-required>
            <label class="field__label">Điểm tổng kết (0-10)</label>
            <input type="number" name="score" class="field__input score-input" step="0.25" min="0" max="10" value="<?= $grade['final_score'] ?? '' ?>" <?= !$canGrade ? 'disabled' : 'required' ?>>
          </div>

          <div class="field mb-4">
            <label class="field__label">Diễn giải điểm</label>
            <textarea name="score_reason" class="field__input" rows="3" <?= !$canGrade ? 'disabled' : '' ?> placeholder="VD: Báo cáo: 5đ, Chuyên cần: 2đ, Điểm doanh nghiệp: 3đ"><?= htmlspecialchars($grade['score_reason'] ?? '') ?></textarea>
          </div>

          <div class="field mb-4">
            <label class="field__label">Nhận xét của GVHD</label>
            <textarea name="feedback" class="field__input" rows="5" <?= !$canGrade ? 'disabled' : '' ?>><?= htmlspecialchars($grade['feedback'] ?? '') ?></textarea>
          </div>
        </form>
      </div>

      <?php if ($canGrade): ?>
        <hr class="separator" />
        <div class="card__footer p-4">
          <button type="submit" form="gradingForm" class="btn w-full" data-variant="primary" data-size="lg">
            <i class="fa-solid fa-save mr-2"></i>Lưu điểm
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Lightbox Overlay -->
<div id="lightbox" class="lightbox-overlay hidden">
  <div class="lightbox-close" title="Đóng"><i class="fa-solid fa-xmark"></i></div>
  <img id="lightbox-img" src="" alt="Phóng to">
</div>

<script src="<?= url('public/js/pages/teacher_grading.js') ?>"></script>