<?php
$layout->start('head'); ?>
<link rel="stylesheet" href="<?= url('public/css/student_weekly_reports.css') ?>">
<?php $layout->end();

$statusMeta = [
  'submitted' => ['Đã nộp', 'success'],
  'late' => ['Nộp muộn', 'warning'],
  'exempt' => ['Không hoạt động', 'secondary'],
  'missing' => ['Chưa nộp', 'destructive'],
  'current' => ['Tuần hiện tại', 'primary'],
  'future' => ['Chưa đến hạn', 'outline']
];
$reasonLabels = [
  'not_started' => 'Chưa bắt đầu tại đơn vị',
  'company_unconfirmed' => 'Chưa xác nhận được nơi thực tập',
  'authorized_leave' => 'Nghỉ có phép',
  'internship_ended' => 'Đã kết thúc thực tập'
];
$defaultWeek = 1;
foreach ($weeks_data as $week) {
  if ($week['status'] === 'current') {
    $defaultWeek = $week['week_number'];
    break;
  }
  if ($week['status'] !== 'future')
    $defaultWeek = $week['week_number'];
}
$timelineWeeks = array_reverse($weeks_data);
?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Báo cáo công việc hằng tuần</h2>
<p class="title-wrapper__description">Đợt thực tập: <span
    class="font-semibold"><?= htmlspecialchars($current['title']) ?></span></p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("student/internship/{$current['id']}") ?>" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-arrow-left mr-2" aria-hidden="true"></i>Thông tin thực tập
</a>
<button type="button" class="btn" data-variant="primary" data-size="md" id="submitBtn"
  data-modal-trigger="#weekly-report-confirm-modal">
  <i class="fa-solid fa-paper-plane mr-2" aria-hidden="true"></i>Lưu báo cáo
</button>
<?php $layout->end() ?>

<?php $layout->start('content') ?>
<div class="detail-layout">
  <div class="card shadow detail-layout__sidebar" aria-labelledby="history-title">
    <div class="card__header">
      <div>
        <h3 class="card__title" id="history-title">Lịch sử báo cáo</h3>
      </div>
    </div>
    <hr class="separator">
    <div class="card__content">
      <div class="weekly-timeline accordion" data-accordion-type="multiple" data-accordion-collapsible>
        <span class="step-wizard__track weekly-timeline__track" aria-hidden="true"></span>
        <?php foreach ($timelineWeeks as $week):
          [$statusLabel, $variant] = $statusMeta[$week['status']];
          $history = $week['history'] ?? []; ?>
          <article class="weekly-timeline__item accordion_item" data-status="<?= $week['status'] ?>"
            data-accordion-value="week-<?= $week['week_number'] ?>" <?= empty($history) ? 'disabled' : '' ?>>
            <span class="step-wizard__trigger weekly-timeline__marker"
              data-step-wizard-state="<?= !empty($history) ? 'passed' : 'upcoming' ?>" aria-hidden="true">
              <?= !empty($history) ? '<i class="fa-solid fa-check"></i>' : (int) $week['week_number'] ?>
            </span>
            <button type="button" class="accordion__trigger weekly-timeline__trigger">
              <span class="weekly-timeline__week">
                <strong>Tuần
                  <?= $week['week_number'] ?>
                </strong>
                <small>
                  <?= date('d/m/Y', strtotime($week['start'])) ?> –
                  <?= date('d/m/Y', strtotime($week['end'])) ?>
                </small>
              </span>
              <span class="weekly-timeline__meta"><span class="badge" data-variant="<?= $variant ?>">
                  <?= $statusLabel ?>
                </span></span>
            </button>
            <?php if (!empty($history)): ?>
              <div class="accordion__content weekly-timeline__content" hidden>
                <div class="weekly-submissions">
                  <?php foreach ($history as $index => $version): ?>
                    <section class="weekly-submission">
                      <div class="weekly-submission__heading">
                        <strong>
                          <?= $index === 0 ? 'Bản hiện tại' : 'Phiên bản ' . (count($history) - $index) ?>
                        </strong><time>
                          <?= date('d/m/Y H:i', strtotime($version['submitted_at'])) ?>
                        </time>
                      </div>
                      <?php if ($version['is_exempt']): ?>
                        <div class="weekly-no-activity">
                          <strong>
                            <?= htmlspecialchars($reasonLabels[$version['no_activity_reason'] ?? ''] ?? 'Không có hoạt động — Chưa phân loại') ?>
                          </strong>
                          <?php if (!empty($version['no_activity_note'])): ?>
                            <p>
                              <?= nl2br(htmlspecialchars($version['no_activity_note'])) ?>
                            </p>
                          <?php endif; ?>
                        </div>
                      <?php else: ?>
                        <div class="weekly-report-copy">
                          <?= nl2br(htmlspecialchars($version['content'])) ?>
                        </div>
                      <?php endif; ?>
                      <?php if ($version['is_late']): ?><span class="badge mt-2" data-variant="warning">Nộp
                          muộn</span>
                      <?php endif; ?>
                      <?php if (!empty($version['images'])): ?>
                        <div class="weekly-attachments">
                          <?php foreach ($version['images'] as $img): ?><a
                              href="<?= url('public/media/' . $img['file_path']) ?>" target="_blank" rel="noopener"><img
                                src="<?= url('public/media/' . $img['file_path']) ?>"
                                alt="<?= htmlspecialchars($img['original_file_name']) ?>"></a>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </section>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="card shadow detail-layout__main">
    <div class="card__header">
      <div>
        <h3 class="card__title">Cập nhật báo cáo</h3>
        <p class="text-sm ">Chọn tuần cần khai báo hoặc chỉnh sửa.</p>
      </div>
    </div>
    <hr class="separator">
    <div class="card__content">
      <form action="<?= url("student/internship/{$current['id']}/weekly_reports") ?>" method="POST"
        enctype="multipart/form-data" id="weeklyReportForm">
        <?= csrf_field() ?>
        <div class="field mb-4" data-field-required>
          <span class="field__label" id="week-select-label">Tuần báo cáo</span>
          <button type="button" class="select w-full" id="weekSelect" data-select-id="weekly-report-week"
            data-select-default-value="<?= $defaultWeek ?>" data-select-placeholder="Chọn tuần"
            aria-labelledby="week-select-label">
            <div class="select__content">
              <?php foreach ($weeks_data as $week):
                if ($week['status'] === 'future')
                  continue;
                [$label] = $statusMeta[$week['status']]; ?>
                <div class="select__item" data-select-value="<?= $week['week_number'] ?>">Tuần <?= $week['week_number'] ?>
                  · <?= date('d/m', strtotime($week['start'])) ?>–<?= date('d/m', strtotime($week['end'])) ?> ·
                  <?= $label ?>
                </div>
              <?php endforeach; ?>
            </div>
          </button>
          <input type="hidden" name="week_number" id="week_number" value="<?= $defaultWeek ?>" required>
        </div>

        <fieldset class="field__set mb-4">
          <legend class="field__label">Tình trạng trong tuần</legend>
          <div class="radio-group grid grid-cols-1 md:grid-cols-2" id="reportMode" data-radio-name="report_mode"
            data-radio-default-value="activity">
            <label class="field__label">
              <div class="field" data-orientation="horizontal"><button class="radio-group__item" type="button"
                  role="radio" value="activity"></button>
                <div>
                  <div class="field__title">Có hoạt động thực tập</div>
                  <p class="field__description">Ghi lại công việc và kết quả.</p>
                </div>
              </div>
            </label>
            <label class="field__label">
              <div class="field" data-orientation="horizontal"><button class="radio-group__item" type="button"
                  role="radio" value="no_activity"></button>
                <div>
                  <div class="field__title">Không có hoạt động</div>
                  <p class="field__description">Chọn lý do phù hợp cho tuần này.</p>
                </div>
              </div>
            </label>
          </div>
        </fieldset>
        <input type="hidden" name="is_exempt" id="is_exempt" value="0">

        <div id="noActivitySection" class="hidden">
          <div class="field mb-4" data-field-required>
            <span class="field__label" id="reason-select-label">Lý do</span>
            <button type="button" class="select w-full" id="reasonSelect" data-select-id="no-activity-reason"
              data-select-placeholder="Chọn lý do" aria-labelledby="reason-select-label">
              <div class="select__content"><?php foreach ($reasonLabels as $value => $label): ?>
                  <div class="select__item" data-select-value="<?= $value ?>"><?= $label ?></div><?php endforeach; ?>
              </div>
            </button>
            <input type="hidden" name="no_activity_reason" id="no_activity_reason">
          </div>
          <div class="field mb-4"><label class="field__label" for="no_activity_note">Ghi chú thêm (không
              bắt buộc)</label>
            <textarea class="field__input" name="no_activity_note" id="no_activity_note" rows="3"
              placeholder="Bổ sung thông tin nếu cần..."></textarea>
          </div>
        </div>

        <div id="reportContentSection">
          <div class="field mb-4" data-field-required><label class="field__label" for="report_content">Nội dung công
              việc</label><textarea name="content" id="report_content" class="field__input" rows="7"
              placeholder="Mô tả công việc, kết quả và điều đã học được..."></textarea></div>
          <div class="field mb-4"><label class="field__label" for="report_images">Hình ảnh đính kèm (không bắt
              buộc)</label>
            <p class="field__description">Tối đa 5 ảnh JPG, PNG hoặc WEBP; mỗi ảnh không quá 5 MB.</p><input type="file"
              name="images[]" id="report_images" class="field__input" accept="image/jpeg,image/png,image/webp" multiple>
            <div id="imagePreviewContainer" class="weekly-image-preview"></div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal" id="weekly-report-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title" id="weeklyConfirmTitle">Xác nhận gửi báo cáo</h3>
    <p class="modal__description" id="weeklyConfirmDescription">Bạn có chắc chắn muốn gửi báo cáo tuần này?</p>
  </div>
  <div class="modal__footer">
    <button type="button" class="btn" data-variant="outline" data-size="lg" data-modal-close>Hủy</button>
    <button type="submit" class="btn" data-variant="primary" data-size="lg" form="weeklyReportForm"
      id="weeklyConfirmSubmit">Xác nhận gửi</button>
  </div>
  <button class="modal__close" type="button" data-modal-close aria-label="Đóng">
    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
  </button>
</div>
<?php $layout->end() ?>

<?php $layout->start('scripts') ?>
<script type="application/json"
  id="weeklyDataJson"><?= json_encode(array_combine(array_column($weeks_data, 'week_number'), $weeks_data), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="<?= url('public/js/accordion.js') ?>"></script>
<script src="<?= url('public/js/pages/student_weekly_reports.js') ?>"></script>
<?php $layout->end() ?>