<?php

/**
 * View: Chấm điểm thực tập (Teacher)
 * Route: /teacher/internship_batches/{batchId}/grade/{batchStudentId}
 */

$batchId = $batchId ?? null;
$batchStudentId = $batchStudentId ?? null;
$data = $data ?? [];
$canGrade = $canGrade ?? false;

$student = $data['student'] ?? [];
$submissions = $data['submissions'] ?? [];
$latestSubmissions = array_values($submissions);
$allSubmissions = $data['all_submissions'] ?? [];
$grade = $data['grade'] ?? null;
$deadline = $data['deadline'] ?? null;
$timeline = $timeline ?? [];
$isLocked = !empty($grade['grade_lock_at']);
$companyName = $student['company_name'] ?? 'Chưa có thông tin';
$selectedScore = isset($grade['final_score']) ? number_format((float) $grade['final_score'], 2, '.', '') : '';

$historyByType = [];
foreach ($allSubmissions as $submission) {
  $type = $submission['type'] ?? 'internship_report';
  $historyByType[$type][] = $submission;
}

$typeLabels = [
  'internship_report' => 'Báo cáo thực tập',
  'evaluation_form' => 'Phiếu đánh giá',
  'company_survey' => 'Khảo sát doanh nghiệp',
  'related_photo' => 'Hình ảnh'
];

$statusMeta = [
  'submitted' => ['Đã nộp', 'success'],
  'late' => ['Nộp muộn', 'warning'],
  'exempt' => ['Nghỉ', 'secondary'],
  'missing' => ['Chưa nộp', 'destructive'],
  'current' => ['Tuần hiện tại', 'primary'],
  'future' => ['Chưa đến', 'outline']
];

$reportStats = ['submitted' => 0, 'late' => 0, 'missing' => 0, 'exempt' => 0];
foreach (($timeline['weeks'] ?? []) as $week) {
  if (isset($reportStats[$week['status']])) {
    $reportStats[$week['status']]++;
  }
}
?>

<link rel="stylesheet" href="<?= url('public/css/student_weekly_reports.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/teacher_grading.css') ?>">

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Chấm điểm thực tập</h2>
<div class="title-wrapper__description flex flex-wrap items-center gap-2">
  <span class="font-semibold"><?= htmlspecialchars($student['full_name'] ?? 'Chưa có') ?></span>
  <span aria-hidden="true">•</span>
  <span>MSSV: <?= htmlspecialchars($student['student_code'] ?? '--') ?></span>
  <span aria-hidden="true">•</span>
  <span>Công ty: <?= htmlspecialchars($companyName) ?></span>
</div>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("teacher/internship_batches/{$batchId}") ?>" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Quay lại
</a>
<?php if ($canGrade && !$isLocked): ?>
  <button type="button" class="btn" data-variant="primary" data-size="md"
    data-modal-trigger="#grading-confirm-modal">
    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Lưu điểm
  </button>
<?php endif; ?>
<?php $layout->end() ?>

<div class="tabs w-full" data-tabs data-tabs-id="teacher-grading" data-tabs-panel-active="evaluation">
  <div class="tabs__list overflow-x-auto" role="tablist" aria-label="Nội dung chấm điểm">
    <a href="#teacher-grading:evaluation" class="tabs__trigger" role="tab" aria-selected="true"
      aria-controls="teacher-grading-panel-evaluation" data-tabs-trigger="evaluation" data-tabs-trigger-state="active"
      tabindex="0">
      <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Đánh giá
    </a>
    <a href="#teacher-grading:documents" class="tabs__trigger" role="tab" aria-selected="false"
      aria-controls="teacher-grading-panel-documents" data-tabs-trigger="documents" data-tabs-trigger-state="idle"
      tabindex="-1">
      <i class="fa-solid fa-file-lines" aria-hidden="true"></i> Tài liệu
    </a>
    <a href="#teacher-grading:weekly" class="tabs__trigger" role="tab" aria-selected="false"
      aria-controls="teacher-grading-panel-weekly" data-tabs-trigger="weekly" data-tabs-trigger-state="idle" tabindex="-1">
      <i class="fa-solid fa-calendar-week" aria-hidden="true"></i> Báo cáo tuần
    </a>
  </div>

  <div id="teacher-grading-panel-evaluation" class="tabs__panel" role="tabpanel" data-tabs-panel="evaluation"
    data-tabs-panel-state="active">
    <div class="card shadow grading-card">
      <div class="card__header">
        <div>
          <h3 class="card__title">Đánh giá kết quả thực tập</h3>
          <p class="card__description text-sm">Chọn điểm tổng kết và ghi rõ căn cứ đánh giá của sinh viên.</p>
        </div>
      </div>
      <hr class="separator">
      <div class="card__content">
        <?php if ($isLocked): ?>
          <div class="alert mb-4" data-variant="success">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            Điểm đã được chốt và công bố.
          </div>
        <?php elseif (!$canGrade): ?>
          <div class="alert mb-4" data-variant="destructive">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            Đã hết hạn chấm điểm.
          </div>
        <?php elseif ($deadline): ?>
          <div class="alert mb-4" data-variant="warning">
            <i class="fa-solid fa-clock" aria-hidden="true"></i>
            Hạn chấm điểm: <?= date('d/m/Y', strtotime($deadline)) ?>.
          </div>
        <?php endif; ?>

        <form action="<?= url("teacher/internship_batches/{$batchId}/grade/{$batchStudentId}") ?>" method="POST"
          id="gradingForm" class="field-group">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="draft">

          <div class="field" data-field-required>
            <span class="field__label" id="score-label">Điểm tổng kết</span>
            <button type="button" class="select w-full" data-select-id="grading-score" name="score" role="listbox"
              aria-labelledby="score-label" data-select-placeholder="Chọn điểm"
              data-select-default-value="<?= htmlspecialchars($selectedScore) ?>" <?= (!$canGrade || $isLocked) ? 'disabled' : '' ?>>
              <div class="select__content">
                <?php for ($quarter = 0; $quarter <= 40; $quarter++):
                  $scoreValue = number_format($quarter / 4, 2, '.', ''); ?>
                  <div class="select__item" data-select-value="<?= $scoreValue ?>"><?= rtrim(rtrim($scoreValue, '0'), '.') ?></div>
                <?php endfor; ?>
              </div>
            </button>
            <p class="field__description">Thang điểm 0–10.</p>
          </div>

          <div class="field">
            <label class="field__label" for="score_reason">Diễn giải điểm</label>
            <textarea id="score_reason" name="score_reason" class="field__input" rows="4"
              placeholder="Ví dụ: Báo cáo 5đ, chuyên cần 2đ, đánh giá doanh nghiệp 1,5đ"
              <?= (!$canGrade || $isLocked) ? 'disabled' : '' ?>><?= htmlspecialchars($grade['score_reason'] ?? '') ?></textarea>
            <p class="field__description">Nêu ngắn gọn các thành phần tạo nên điểm tổng kết.</p>
          </div>

          <div class="field">
            <label class="field__label" for="feedback">Nhận xét của GVHD</label>
            <textarea id="feedback" name="feedback" class="field__input" rows="6"
              placeholder="Nhận xét về thái độ, tiến độ và kết quả thực tập"
              <?= (!$canGrade || $isLocked) ? 'disabled' : '' ?>><?= htmlspecialchars($grade['feedback'] ?? '') ?></textarea>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div id="teacher-grading-panel-documents" class="tabs__panel" role="tabpanel" data-tabs-panel="documents"
    data-tabs-panel-state="idle">
    <div class="card shadow">
      <div class="card__header">
        <div>
          <h3 class="card__title">Tài liệu thực tập</h3>
          <p class="card__description text-sm">Xem tài liệu sinh viên đã nộp.</p>
        </div>
      </div>
      <hr class="separator">
      <div class="card__content">
        <?php if (empty($latestSubmissions)): ?>
          <div class="empty">
            <div class="empty__header">
              <div class="empty__media"><i class="fa-solid fa-file-circle-xmark" aria-hidden="true"></i></div>
              <div class="empty__title">Chưa có tài liệu</div>
              <div class="empty__description">Sinh viên chưa nộp bất kỳ tài liệu thực tập nào.</div>
            </div>
          </div>
        <?php else: ?>
          <div class="tabs" data-tabs data-tabs-id="submission-viewer"
            data-tabs-panel-active="submission-<?= (int) $latestSubmissions[0]['id'] ?>">
            <div class="tabs__list overflow-x-auto" role="tablist" aria-label="Tài liệu đã nộp">
              <?php foreach ($latestSubmissions as $index => $submission):
                $submissionKey = 'submission-' . (int) $submission['id'];
                $isActive = $index === 0; ?>
                <a href="#submission-viewer:<?= $submissionKey ?>" class="tabs__trigger" role="tab"
                  aria-selected="<?= $isActive ? 'true' : 'false' ?>"
                  aria-controls="submission-viewer-panel-<?= $submissionKey ?>" data-tabs-trigger="<?= $submissionKey ?>"
                  data-tabs-trigger-state="<?= $isActive ? 'active' : 'idle' ?>" tabindex="<?= $isActive ? '0' : '-1' ?>">
                  <?= htmlspecialchars($typeLabels[$submission['type'] ?? 'internship_report'] ?? 'Tài liệu') ?>
                </a>
              <?php endforeach; ?>
            </div>

            <?php foreach ($latestSubmissions as $index => $submission):
              $type = $submission['type'] ?? 'internship_report';
              $history = $historyByType[$type] ?? [];
              $submissionKey = 'submission-' . (int) $submission['id'];
              $isActive = $index === 0; ?>
              <div id="submission-viewer-panel-<?= $submissionKey ?>" class="tabs__panel submission-pane"
                role="tabpanel" data-tabs-panel="<?= $submissionKey ?>"
                data-tabs-panel-state="<?= $isActive ? 'active' : 'idle' ?>">
                <?php if ($type === 'related_photo'): ?>
                  <div class="image-gallery grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                    <?php foreach ($history as $imageSubmission): ?>
                      <figure class="card shadow-sm p-3">
                        <img src="<?= url("api/v1/teacher/submissions/{$imageSubmission['id']}/preview") ?>"
                          class="js-lightbox-trigger image-gallery__img" alt="<?= htmlspecialchars($imageSubmission['original_file_name']) ?>">
                        <figcaption class="text-xs mt-2">
                          <?= date('d/m/Y H:i', strtotime($imageSubmission['submitted_at'])) ?> ·
                          <?= htmlspecialchars($imageSubmission['original_file_name']) ?>
                        </figcaption>
                      </figure>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <?php if (count($history) > 1): ?>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 p-3 border">
                      <label class="text-sm font-medium" for="version-<?= (int) $submission['id'] ?>">Phiên bản tài liệu</label>
                      <select id="version-<?= (int) $submission['id'] ?>" class="field__input js-version-select md:w-fit"
                        data-target="iframe-<?= (int) $submission['id'] ?>">
                        <?php foreach ($history as $historyIndex => $historySubmission): ?>
                          <option value="<?= url("api/v1/teacher/submissions/{$historySubmission['id']}/preview") ?>">
                            <?= date('d/m/Y H:i', strtotime($historySubmission['submitted_at'])) ?><?= $historyIndex === 0 ? ' (Mới nhất)' : '' ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  <?php endif; ?>
                  <iframe id="iframe-<?= (int) $submission['id'] ?>"
                    src="<?= url("api/v1/teacher/submissions/{$submission['id']}/preview") ?>" class="pdf-viewer"
                    title="<?= htmlspecialchars($typeLabels[$type] ?? 'Tài liệu thực tập') ?>"></iframe>
                  <div class="flex justify-end mt-3">
                    <a class="btn" data-variant="outline" data-size="sm"
                      href="<?= url("api/v1/teacher/submissions/{$submission['id']}/preview") ?>" target="_blank" rel="noopener">
                      <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>Mở trong tab mới
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div id="teacher-grading-panel-weekly" class="tabs__panel" role="tabpanel" data-tabs-panel="weekly"
    data-tabs-panel-state="idle">
    <div class="card shadow">
      <div class="card__header">
        <div>
          <h3 class="card__title">Tiến độ báo cáo tuần</h3>
          <p class="card__description text-sm">Theo dõi tiến độ và mở từng tuần để xem nội dung chi tiết.</p>
        </div>
      </div>
      <hr class="separator">
      <div class="card__content">
        <?php if (empty($timeline['weeks'])): ?>
          <div class="empty">
            <div class="empty__header">
              <div class="empty__media"><i class="fa-solid fa-calendar-xmark" aria-hidden="true"></i></div>
              <div class="empty__title">Chưa có lịch báo cáo tuần</div>
              <div class="empty__description">Lịch báo cáo sẽ hiển thị khi đợt thực tập có tuần báo cáo.</div>
            </div>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="card p-3"><span class="text-xs">Tổng số</span><strong class="block text-xl"><?= count($timeline['weeks']) ?></strong></div>
            <div class="card p-3"><span class="text-xs">Đã nộp</span><strong class="block text-xl"><?= $reportStats['submitted'] + $reportStats['late'] ?></strong></div>
            <div class="card p-3"><span class="text-xs">Nộp muộn</span><strong class="block text-xl"><?= $reportStats['late'] ?></strong></div>
            <div class="card p-3"><span class="text-xs">Chưa nộp</span><strong class="block text-xl"><?= $reportStats['missing'] ?></strong></div>
          </div>

          <div class="weekly-timeline accordion" data-accordion-type="multiple" data-accordion-collapsible>
            <span class="step-wizard__track weekly-timeline__track" aria-hidden="true"></span>
            <?php foreach ($timeline['weeks'] as $week):
              [$statusLabel, $statusVariant] = $statusMeta[$week['status']] ?? [$week['status'], 'secondary'];
              $hasReport = !empty($week['report']); ?>
              <article class="weekly-timeline__item accordion_item" data-status="<?= htmlspecialchars($week['status']) ?>"
                data-accordion-value="teacher-week-<?= (int) $week['week_number'] ?>" <?= !$hasReport ? 'disabled' : '' ?>>
                <span class="step-wizard__trigger weekly-timeline__marker"
                  data-step-wizard-state="<?= $hasReport ? 'passed' : 'upcoming' ?>" aria-hidden="true">
                  <?= $hasReport ? '<i class="fa-solid fa-check"></i>' : (int) $week['week_number'] ?>
                </span>
                <button type="button" class="accordion__trigger weekly-timeline__trigger">
                  <span class="weekly-timeline__week flex-1">
                    <strong>Tuần <?= (int) $week['week_number'] ?></strong>
                    <small><?= date('d/m/Y', strtotime($week['start'])) ?> – <?= date('d/m/Y', strtotime($week['end'])) ?></small>
                  </span>
                  <span class="weekly-timeline__meta"><span class="badge" data-variant="<?= $statusVariant ?>"><?= $statusLabel ?></span></span>
                </button>
                <?php if ($hasReport): ?>
                  <div class="accordion__content weekly-timeline__content" hidden>
                    <div class="weekly-submissions">
                      <section class="weekly-submission">
                        <?php if (!empty($week['report']['is_exempt'])): ?>
                          <div class="weekly-no-activity"><strong>Không thực tập trong tuần này.</strong></div>
                        <?php else: ?>
                          <div class="weekly-report-copy"><?= nl2br(htmlspecialchars($week['report']['content'])) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($week['report']['images'])): ?>
                          <div class="weekly-attachments">
                            <?php foreach ($week['report']['images'] as $image): ?>
                              <a href="<?= url('public/media/' . $image['file_path']) ?>" target="_blank" rel="noopener">
                                <img src="<?= url('public/media/' . $image['file_path']) ?>" alt="<?= htmlspecialchars($image['original_file_name']) ?>">
                              </a>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                        <time class="block text-xs mt-3">Nộp lúc: <?= date('d/m/Y H:i', strtotime($week['report']['submitted_at'])) ?></time>
                      </section>
                    </div>
                  </div>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="grading-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận lưu điểm</h3>
    <p class="modal__description">Lưu điểm và nhận xét hiện tại cho <?= htmlspecialchars($student['full_name'] ?? 'sinh viên') ?>?</p>
  </div>
  <div class="modal__footer">
    <button type="button" class="btn" data-variant="outline" data-size="lg" data-modal-close>Hủy</button>
    <button type="submit" class="btn" data-variant="primary" data-size="lg" form="gradingForm">Xác nhận lưu</button>
  </div>
  <button type="button" class="modal__close" data-modal-close aria-label="Đóng">
    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
  </button>
</div>

<div id="lightbox" class="lightbox-overlay hidden" role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to">
  <button type="button" class="lightbox-close" aria-label="Đóng"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
  <img id="lightbox-img" src="" alt="Ảnh phóng to">
</div>

<?php $layout->start('scripts') ?>
<script src="<?= url('public/js/accordion.js') ?>"></script>
<script src="<?= url('public/js/pages/teacher_grading.js') ?>" type="module"></script>
<?php $layout->end() ?>
