<?php

use App\Enums\BatchStatus;
use App\Models\InternshipBatch;
use App\Core\AppTime;

/**
 * View: Thông tin thực tập sinh viên
 * Route: /student/internship
 * SEO: Thông tin chi tiết về đợt thực tập của sinh viên
 */
$student = $student ?? null;
$current = $current ?? null;
$batches = $batches ?? [];
$supervisor = $supervisor ?? null;
$submissions = $submissions ?? [];
$logs = $logs ?? [];
$grade = $grade ?? null;
$journey = $journey ?? ['active_phase' => 0, 'phase_states' => ['active', 'upcoming', 'upcoming']];

$batchModel = new InternshipBatch();
if ($current) {
  $batchModel->status = $current['status'] ?? 'draft';
  $batchModel->start_at = $current['start_at'] ?? null;
  $batchModel->end_at = $current['end_at'] ?? null;
}
$effStatus = $current ? $batchModel->getEffectiveStatus() : null;
$effectiveMetadata = $effStatus ? [
  'label' => BatchStatus::getLabel($effStatus),
  'variant' => BatchStatus::getVariant($effStatus)
] : null;
?>

<link rel="stylesheet" href="<?= url('public/css/student_dashboard.css') ?>">
<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Thông tin thực tập
</h2>
<p class="title-wrapper__description">
  <?= $current ? 'Xem chi tiết đợt thực tập và kết quả đánh giá.' : 'Chọn một đợt thực tập để xem thông tin và tiến độ.' ?>
</p>
<?php $layout->end() ?>

<?php if ($current): ?>
  <?php $layout->start("actions") ?>
  <a href="<?= url('student/internship') ?>" class="btn" data-variant="outline" data-size="md">
    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
    Danh sách đợt
  </a>
  <a href="<?= url("student/internship/{$current['id']}/referral_letters/create") ?>" class="btn" data-variant="primary"
    data-size="md">
    <i class="fa-solid fa-plus mr-1" aria-hidden="true"></i>
    Đăng ký giấy giới thiệu
  </a>
  <?php $layout->end() ?>
<?php endif; ?>

<?php if (!$current): ?>
  <?php if (empty($batches)): ?>
    <div class="card shadow py-12 text-center">
      <div class="card__content empty">
        <div class="empty__header">
          <div class="empty__media"><i class="fa-solid fa-calendar-xmark" aria-hidden="true"></i></div>
          <h3 class="empty__title">Chưa tham gia đợt thực tập nào</h3>
          <p class="empty__description">Thông tin sẽ hiển thị sau khi bạn được thêm vào một đợt thực tập.</p>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="tm-container" data-tm="student_batches_table" data-tm-mode="client" data-tm-searchable>
      <template data-tm-col="title" data-tm-label="Tên đợt" data-tm-sortable data-tm-filter-type="text">
        <a href="{{ row._href }}" class="font-medium">{{ value }}</a>
      </template>
      <template data-tm-col="start_at_label" data-tm-label="Bắt đầu" data-tm-sortable></template>
      <template data-tm-col="end_at_label" data-tm-label="Kết thúc" data-tm-sortable></template>
      <template data-tm-col="effective_status" data-tm-label="Trạng thái" data-tm-filter-type="select"
        data-tm-filter-options='<?= json_encode(BatchStatus::getEffectiveOptions()) ?>'>
        <span class="badge" data-variant="{{ row.effective_status_variant }}">{{ row.effective_status_label }}</span>
      </template>
      <template data-tm-pagination></template>
    </div>
  <?php endif; ?>
<?php elseif ($current['status'] !== 'draft'): ?>
  <section class="internship-overview card shadow" aria-labelledby="internship-overview-title">
    <div class="card__content">
      <div class="internship-overview__heading">
        <div>
          <h3 id="internship-overview-title" class="internship-overview__title">
            <?= htmlspecialchars($current['title'] ?? '') ?>
          </h3>
        </div>
        <?php if ($effectiveMetadata): ?>
          <span class="badge" data-variant="<?= $effectiveMetadata['variant'] ?>"><?= $effectiveMetadata['label'] ?></span>
        <?php endif; ?>
      </div>
      <div class="internship-summary">
        <div class="internship-summary__item">
          <span class="internship-summary__label">Thời gian</span>
          <span class="internship-summary__value"><?= date('d/m/Y', strtotime($current['start_at'])) ?> –
            <?= date('d/m/Y', strtotime($current['end_at'])) ?></span>
        </div>
        <div class="internship-summary__item">
          <span class="internship-summary__label">Giảng viên hướng dẫn</span>
          <span
            class="internship-summary__value"><?= $supervisor ? htmlspecialchars($supervisor->full_name) : 'Chưa phân công' ?></span>
        </div>
        <div class="internship-summary__item internship-summary__item--next">
          <span class="internship-summary__label">Việc cần làm</span>
          <span class="internship-summary__value"><?= htmlspecialchars($journey['next_action'] ?? '') ?></span>
        </div>
      </div>
    </div>
  </section>

  <?php
  $phaseLabels = ['Chuẩn bị', 'Thực tập', 'Chấm điểm & kết thúc'];
  $phaseIcons = ['fa-clipboard-check', 'fa-briefcase', 'fa-graduation-cap'];
  $phaseStateLabels = ['passed' => 'Đã hoàn thành', 'active' => 'Đang diễn ra', 'upcoming' => 'Sắp tới'];
  $phaseStateIcons = ['passed' => 'fa-circle-check', 'active' => 'fa-circle-dot', 'upcoming' => 'fa-circle'];
  ?>
  <nav class="internship-phase-tabs" data-tabs data-tabs-id="internship-journey"
    data-tabs-panel-active="phase-<?= (int) $journey['active_phase'] ?>" data-tabs-sync="false"
    aria-label="Các giai đoạn thực tập">
    <div class="internship-phase-tabs__list" role="tablist">
      <?php foreach ($phaseLabels as $index => $label): ?>
        <?php
        $phaseState = $journey['phase_states'][$index] ?? 'upcoming';
        $phaseStatusLabel = $phaseStateLabels[$phaseState];
        if ($index === 1 && !empty($weekly_summary)) {
          $phaseStatusLabel = (int) $weekly_summary['submitted_weeks'] . ' / ' . (int) $weekly_summary['total_weeks'] . ' tuần đã nộp';
        }
        ?>
        <button type="button" class="internship-phase-tab" role="tab" id="internship-phase-tab-<?= $index ?>"
          data-phase-state="<?= $phaseState ?>" data-tabs-trigger="phase-<?= $index ?>"
          aria-controls="internship-phase-<?= $index ?>"
          aria-selected="<?= $index === (int) $journey['active_phase'] ? 'true' : 'false' ?>">
          <span class="internship-phase-tab__meta">
            <i class="fa-regular <?= $phaseStateIcons[$phaseState] ?>" aria-hidden="true"></i>
            <span>Bước <?= $index + 1 ?></span>
          </span>
          <span class="internship-phase-tab__title"><i class="fa-solid <?= $phaseIcons[$index] ?>"
              aria-hidden="true"></i><?= $label ?></span>
          <span class="internship-phase-tab__status"><?= $phaseStatusLabel ?></span>
        </button>
      <?php endforeach; ?>
    </div>
  </nav>

  <div class="internship-phase-heading" data-internship-phase-heading aria-live="polite">
    <p class="internship-phase-heading__kicker">Giai đoạn <?= (int) $journey['active_phase'] + 1 ?>/3</p>
    <h3><?= $phaseLabels[(int) $journey['active_phase']] ?></h3>
  </div>
  <div class="detail-layout">
    <div class="detail-layout__main">
      <!-- Khai báo công ty -->
      <div class="internship-task" data-internship-phase="1" data-tabs-observe="internship-journey:phase-1"
        id="internship-phase-1" role="tabpanel" aria-labelledby="internship-phase-tab-1">
        <?php
        $deadlineDt = $company_deadline ? new \DateTime($company_deadline) : null;
        $now = AppTime::now();
        ?>
          <?php if ($can_edit_company): ?>
            <form action="<?= url("student/internship/{$current['id']}/company") ?>" method="POST" id="companyForm">
              <?= csrf_field() ?>
              <input type="hidden" name="batch_student_id" value="<?= $current['batch_student_id'] ?>">

              <div class="grid gap-4">
              <div class="card shadow-sm">
              <fieldset class="field__set">
                <div class="card__header">
                  <legend class="field__legend"><i class="fa-solid fa-building mr-2" aria-hidden="true"></i>Thông tin công ty và kỳ thực tập</legend>
                  <p class="field__description">Khai báo doanh nghiệp, vị trí và thời gian thực tập.<?php if ($deadlineDt): ?> Hạn chót khai báo: <strong><?= $deadlineDt->format('d/m/Y') ?></strong>.<?php endif; ?></p>
                </div>
                <hr class="separator">
                <div class="card__content">
                <div class="field-group">

              <div class="field mb-3" data-orientation="horizontal">
                <input type="checkbox" id="is_manual" name="is_manual" value="1" class="field__input">
                <label for="is_manual" class="field__label">Tôi không tìm thấy mã số thuế / Công ty chưa có mã số
                  thuế</label>
              </div>

              <div class="field mb-3" data-field-required>
                <label class="field__label">Mã số thuế</label>
                <div class="field__input-group">
                  <input type="text" name="tax_code" id="tax_code" class="field__input" required
                    value="<?= htmlspecialchars($current['company_tax_code'] ?? '') ?>">
                  <button type="button" id="btnCheckMST" data-variant="outline" data-size="md" class="btn">Kiểm
                    tra</button>
                </div>
                <div id="mstLoading" class="field__description hidden"><i class="fa-solid fa-spinner fa-spin"></i> Đang
                  tải
                  thông tin...</div>
                <div id="mstError" class="field__error hidden"></div>
              </div>

              <div class="field mb-3" data-field-required>
                <label class="field__label">Tên công ty</label>
                <div class="field__suggest-wrapper">
                  <input type="text" name="name" id="company_name" class="field__input relative" required
                    value="<?= htmlspecialchars($current['company_name'] ?? '') ?>" readonly autocomplete="off">
                  <div id="companySuggestions" class="suggestions-list hidden"></div>
                </div>
              </div>

              <div class="field mb-3" data-field-required>
                <label class="field__label">Địa chỉ</label>
                <textarea name="address" id="company_address" class="field__input" required
                  readonly><?= htmlspecialchars($current['company_address'] ?? '') ?></textarea>
              </div>

              <div class="field mb-3" data-field-required>
                <label class="field__label">Vị trí thực tập</label>
                <input type="text" name="position" class="field__input" required
                  value="<?= htmlspecialchars($current['position'] ?? '') ?>" placeholder="VD: Thực tập sinh Frontend">
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                <div class="field" data-field-required>
                  <label class="field__label">Từ ngày</label>
                  <input type="date" name="internship_start_date" class="field__input" required
                    value="<?= htmlspecialchars($current['internship_start_date'] ?? '') ?>">
                </div>
                <div class="field" data-field-required>
                  <label class="field__label">Đến ngày</label>
                  <input type="date" name="internship_end_date" class="field__input" required
                    value="<?= htmlspecialchars($current['internship_end_date'] ?? '') ?>">
                </div>
              </div>
                </div>
                </div>
              </fieldset>
              </div>

              <div class="card shadow-sm">
              <fieldset class="field__set">
                <div class="card__header">
                    <legend class="field__legend"><i class="fa-solid fa-user-tie mr-2" aria-hidden="true"></i>Cán bộ hướng dẫn tại doanh nghiệp</legend>
                  <p class="field__description">Thông tin người trực tiếp phụ trách bạn trong thời gian thực tập.</p>
                </div>
                <hr class="separator">
                <div class="card__content">
                <div class="field-group">
                <div class="field" data-field-required><label class="field__label">Họ tên</label><input type="text" name="company_mentor_name" class="field__input" required maxlength="255" value="<?= htmlspecialchars($current['company_mentor_name'] ?? '') ?>"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div class="field" data-field-required><label class="field__label">Số điện thoại</label><input type="tel" name="company_mentor_phone" class="field__input" required maxlength="20" value="<?= htmlspecialchars($current['company_mentor_phone'] ?? '') ?>"></div>
                  <div class="field" data-field-required><label class="field__label">Email</label><input type="email" name="company_mentor_email" class="field__input" required maxlength="255" value="<?= htmlspecialchars($current['company_mentor_email'] ?? '') ?>"></div>
                </div>
                </div>
                </div>
              </fieldset>
              </div>
              </div>

              <div class="flex justify-end mt-2">
                <button type="submit" class="btn" data-variant="primary" data-size="lg">Lưu thông tin</button>
              </div>
            </form>
          <?php else: ?>
            <?php if ($current['company_name']): ?>
              <div class="grid gap-2">
                <p><span class="font-bold">Tên công ty:</span>
                  <?= htmlspecialchars($current['company_name'] ?? 'Chưa có') ?>
                </p>
                <p><span class="font-bold">MST:</span> <?= htmlspecialchars($current['company_tax_code'] ?? 'Chưa có') ?>
                </p>
                <p><span class="font-bold">Địa chỉ:</span> <?= htmlspecialchars($current['company_address'] ?? 'Chưa có') ?>
                </p>
                <p><span class="font-bold">Vị trí:</span> <?= htmlspecialchars($current['position'] ?? 'Chưa có') ?></p>
                <p><span class="font-bold">Thời gian thực tập:</span>
                  <?php if ($current['internship_start_date'] && $current['internship_end_date']): ?>
                    từ <?= htmlspecialchars(date("d/m/Y", strtotime($current['internship_start_date']))) ?> đến
                    <?= htmlspecialchars(date("d/m/Y", strtotime($current['internship_end_date']))) ?>
                  <?php else: ?>
                    Chưa có
                  <?php endif; ?>
                </p>
              </div>
            <?php else: ?>
              <?php if (empty($recent_referral_letters)): ?>
                <div class="card shadow-sm">
                  <div class="card__content">
                    <div class="empty-state"><p class="text-sm">Bạn chưa khai báo thông tin công ty thực tập.</p></div>
                  </div>
                </div>
              <?php else: ?>
              <div class="space-y-3">
                <?php foreach ($recent_referral_letters as $rl): ?>
                  <div class="border rounded p-3 text-sm">
                    <div class="font-bold mb-1" title="<?= htmlspecialchars($rl['company_name']) ?>">
                      <?= htmlspecialchars($rl['company_name']) ?>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                      <span class="text-xs"><?= date('d/m/Y', strtotime($rl['created_at'])) ?></span>
                      <?php
                      $statusMap = [
                        'pending' => ['Chờ duyệt', 'secondary'],
                        'approved' => ['Đang xử lý', 'secondary'],
                        'completed' => ['Hoàn thành', 'success'],
                        'received' => ['Đã nhận', 'success'],
                        'rejected' => ['Từ chối', 'destructive'],
                        'cancelled' => ['Đã hủy', 'destructive'],
                      ];
                      [$label, $variant] = $statusMap[$rl['status']] ?? [$rl['status'], 'outline'];
                      ?>
                      <span class="badge" data-variant="<?= $variant ?>"><?= $label ?></span>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            <?php endif; ?>
          <?php endif; ?>
      </div>

      <!-- Giấy giới thiệu -->
      <div class="card shadow internship-task" data-internship-phase="0" data-tabs-observe="internship-journey:phase-0">
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-file-contract mr-2"></i>
            Giấy giới thiệu
          </h3>
          <?php if ($current): ?>
            <a href="<?= url("student/internship/{$current['id']}/referral_letters/create") ?>" class="card__action btn"
              data-variant="primary" data-size="lg">
              <i class="fa-solid fa-plus mr-1"></i>
              Đăng ký
            </a>
          <?php endif; ?>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <?php if (empty($recent_referral_letters)): ?>
            <div class="empty-state">
              <p class="text-sm">Bạn chưa đăng ký giấy giới thiệu nào.</p>
            </div>
          <?php else: ?>
            <div class="space-y-3">
              <?php foreach ($recent_referral_letters as $rl): ?>
                <div class="border rounded p-3 text-sm">
                  <div class="font-bold mb-1" title="<?= htmlspecialchars($rl['company_name']) ?>">
                    <?= htmlspecialchars($rl['company_name']) ?>
                  </div>
                  <div class="flex justify-between items-center mt-2">
                    <span class="text-xs"><?= date('d/m/Y', strtotime($rl['created_at'])) ?></span>
                    <?php
                    $statusMap = [
                      'pending' => ['Chờ duyệt', 'secondary'],
                      'approved' => ['Đang xử lý', 'secondary'],
                      'completed' => ['Hoàn thành', 'success'],
                      'received' => ['Đã nhận', 'success'],
                      'rejected' => ['Từ chối', 'destructive'],
                      'cancelled' => ['Đã hủy', 'destructive'],
                    ];
                    [$label, $variant] = $statusMap[$rl['status']] ?? [$rl['status'], 'outline'];
                    ?>
                    <span class="badge" data-variant="<?= $variant ?>"><?= $label ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="card__footer">
          <a href="<?= url("student/internship/{$current['id']}/referral_letters") ?>" class="btn w-full"
            data-variant="outline" data-size="sm">
            <?= $total_referral_letters ? 'Xem tất cả (' . $total_referral_letters . ')' : 'Quản lý giấy giới thiệu' ?>
          </a>
        </div>
      </div>
      <?php if ($current): ?>
        <div id="internship-data" data-batch-student-id="<?= $current['batch_student_id'] ?>" class="hidden"></div>
      <?php endif; ?>


      <!-- Kết quả -->
      <div class="card shadow result-card internship-task" data-internship-phase="2"
        data-tabs-observe="internship-journey:phase-2" id="internship-phase-2" role="tabpanel"
        aria-labelledby="internship-phase-tab-2">
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-star mr-2"></i>
            Kết quả thực tập
          </h3>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="flex-1 w-full">

            <?php if ($grade && isset($grade['final_score']) && isset($grade['grade_lock_at']) && $grade['grade_lock_at'] !== null): ?>
              <div class="font-bold text-4xl text-center">
                <?= $grade['final_score'] ?>
              </div>

              <div class="flex justify-between gap-2 mt-2">
                <?php if (!empty($grade['score_reason'])): ?>
                  <div class="flex-1 p-2">
                    <p>
                      <span class="font-semibold">Chi tiết điểm:</span>
                    </p>
                    <p>
                      <?= nl2br(htmlspecialchars($grade['score_reason'])) ?>
                    </p>
                  </div>
                <?php endif; ?>

                <?php if (!empty($grade['feedback'])): ?>
                  <div class="flex-1 p-2" style="border-left: 1px solid var(--border)">
                    <p>
                      <span class="font-semibold">Nhận xét của GV:</span>
                    </p>
                    <p>
                      <?= nl2br(htmlspecialchars($grade['feedback'])) ?>
                    </p>
                  </div>
                <?php endif; ?>
              </div>

            <?php elseif (($journey['grade_state'] ?? 'waiting') === 'graded'): ?>
              <div class="internship-grade-state">
                <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
                <div><strong>Đã chấm, đang chờ chốt điểm</strong>
                  <p>Điểm sẽ hiển thị sau khi Khoa CNTT công bố điểm chính thức.</p>
                </div>
              </div>
            <?php else: ?>
              <div class="internship-grade-state">
                <i class="fa-solid fa-user-check" aria-hidden="true"></i>
                <div><strong>Chờ giảng viên chấm điểm</strong>
                  <p>Sau khi điểm được chốt, kết quả và nhận xét sẽ xuất hiện tại đây. Admin sẽ thực hiện export theo quy
                    trình nội bộ.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="detail-layout__sidebar">
      <!-- Chi tiết phân công -->
      <div class="card shadow internship-task" data-internship-phase="0" data-tabs-observe="internship-journey:phase-0"
        id="internship-phase-0" role="tabpanel" aria-labelledby="internship-phase-tab-0">
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-circle-info mr-2"></i>
            Chi tiết phân công
          </h3>
        </div>
        <hr class="separator" />
        <?php
        $assignmentRows = [
          ['label' => 'Đợt thực tập', 'value' => htmlspecialchars($current['title'] ?? '')],
          ['label' => 'Thời gian', 'value' => date('d/m/Y', strtotime($current['start_at'])) . ' – ' . date('d/m/Y', strtotime($current['end_at']))],
          ['label' => 'Giảng viên hướng dẫn', 'value' => $supervisor ? htmlspecialchars($supervisor->full_name) : 'Chưa phân công'],
        ];
        if ($supervisor) {
          $assignmentRows[] = ['label' => 'Email GVHD', 'value' => htmlspecialchars($supervisor->account->email)];
          $assignmentRows[] = ['label' => 'Số điện thoại GVHD', 'value' => htmlspecialchars($supervisor->phone)];
        }
        ?>
        <div class="card__content space-y-4">
          <?php foreach ($assignmentRows as $rowIndex => $row): ?>
            <dl class="flex justify-between gap-4">
              <dt><?= $row['label'] ?></dt>
              <dd class="text-right font-medium"><?= $row['value'] ?></dd>
            </dl>
            <?php if ($rowIndex < count($assignmentRows) - 1): ?>
              <hr class="separator">
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Báo cáo hàng tuần -->
      <div class="card shadow internship-task" data-internship-phase="1" data-tabs-observe="internship-journey:phase-1">
        <div class="card__header flex justify-between items-center">
          <h3 class="card__title">
            <i class="fa-solid fa-calendar-week mr-2"></i>
            Báo cáo hàng tuần
          </h3>
          <a href="<?= url("student/internship/{$current['id']}/weekly_reports") ?>" class="btn" data-variant="primary"
            data-size="sm">
            Báo cáo <i class="fa-solid fa-arrow-right ml-1" aria-hidden="true"></i>
          </a>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <?php if (!empty($weekly_summary['recent_reports'])): ?>
            <?php $reportStatusMeta = [
              'submitted' => ['Đã nộp', 'success'],
              'late' => ['Nộp muộn', 'warning'],
              'exempt' => ['Không hoạt động', 'secondary']
            ]; ?>
            <?php $recentWeeklyReports = array_values($weekly_summary['recent_reports']); ?>
            <div class="internship-weekly-history flex flex-col gap-2">
              <?php foreach ($recentWeeklyReports as $reportIndex => $recentReport): ?>
                <?php [$recentLabel, $recentVariant] = $reportStatusMeta[$recentReport['status']]; ?>
                <div class="internship-weekly-history__item">
                  <span><strong>Tuần
                      <?= (int) $recentReport['week_number'] ?></strong><small><?= date('d/m/Y H:i', strtotime($recentReport['submitted_at'])) ?></small></span>
                  <span class="badge" data-variant="<?= $recentVariant ?>"><?= $recentLabel ?></span>
                </div>
                <?php if ($reportIndex < count($recentWeeklyReports) - 1): ?>
                  <hr class="separator">
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-sm" style="color: var(--muted-foreground);">Chưa có báo cáo tuần nào.</p>
          <?php endif; ?>
        </div>
      </div>


      <!-- Nộp tài liệu -->
      <div class="card shadow internship-task" data-internship-phase="1" data-tabs-observe="internship-journey:phase-1">
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-cloud-arrow-up mr-2"></i>
            Nộp tài liệu thực tập tốt nghiệp
          </h3>
          <div class="card__header-meta">
            <?php if ($report_deadline): ?>
              <?php
              $rdDt = new \DateTime($report_deadline);
              $isRNear = $now >= (clone $rdDt)->modify("-{$report_warning_days} days") && $now <= $rdDt;
              $isRPassed = $now > $rdDt;
              ?>
              <?php if ($isRPassed): ?>
                <span class="badge" data-variant="destructive">
                  <i class="fa-solid fa-lock mr-1"></i>Hết hạn nộp</span>
              <?php elseif ($isRNear): ?>
                <span class="badge" data-variant="warning">
                  <i class="fa-solid fa-triangle-exclamation mr-1"></i>Sắp hết hạn</span>
              <?php endif; ?>
              <p class="text-xs">Hạn chót nộp: <span class="font-semibold"><?= $rdDt->format('d/m/Y') ?></span></p>
            <?php endif; ?>
          </div>
          <p class="text-xs">Tài liệu được gửi cho giảng viên hướng dẫn để đánh giá kết quả thực tập.</p>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <form action="<?= url("student/internship/{$current['id']}/upload") ?>" method="POST"
            enctype="multipart/form-data" id="uploadForm">
            <?= csrf_field() ?>
            <input type="hidden" name="batch_student_id" value="<?= $current['batch_student_id'] ?>">

            <div class="mb-4 text-sm" style="color: var(--muted-foreground);">
              Định dạng hỗ trợ: PDF (cho Báo cáo, phiếu đánh giá, khảo sát), Hình ảnh (JPG, PNG, WEBP). Dung lượng tối đa:
              <?= $max_file_size_mb ?>MB
            </div>

            <div class="field mb-3" data-field-required>
              <label class="field__label">Báo cáo thực tập</label>
              <input type="file" name="file_internship_report" id="file_internship_report" class="field__input file-input"
                accept=".pdf" <?= empty($submissions_by_type['internship_report']) ? 'required' : '' ?>>
            </div>

            <div class="field mb-3" data-field-required>
              <label class="field__label">Phiếu đánh giá thực tập</label>
              <input type="file" name="file_evaluation_form" id="file_evaluation_form" class="field__input file-input"
                accept=".pdf" <?= empty($submissions_by_type['evaluation_form']) ? 'required' : '' ?>>
            </div>

            <div class="field mb-3">
              <label class="field__label">Phiếu khảo sát doanh nghiệp</label>
              <input type="file" name="file_company_survey" id="file_company_survey" class="field__input file-input"
                accept=".pdf">
            </div>

            <div class="field mb-3">
              <label class="field__label">Hình ảnh liên quan</label>
              <input type="file" name="file_related_photo[]" id="file_related_photo" class="field__input file-input"
                accept="image/jpeg,image/png,image/webp" multiple>
              <p class="field__description">Tối đa 5 ảnh</p>
            </div>

            <div class="mt-4 flex justify-end">
              <?php if ($can_submit_report): ?>
                <button type="submit" class="btn" data-variant="primary" data-size="lg" disabled id="uploadBtn">Nộp tài
                  liệu</button>
              <?php else: ?>
                <button type="button" class="btn" data-variant="primary" data-size="lg"
                  disabled><?= htmlspecialchars($cannot_submit_reason ?? 'Không thể nộp') ?></button>
              <?php endif; ?>
            </div>
          </form>
        </div>
        <hr class="separator" />
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-clock-rotate-left mr-2"></i>
            Lịch sử nộp
          </h3>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="timeline-container">
            <?php if (empty($submissions)): ?>
              <div class="empty-state">
                <p class="text-sm">Bạn chưa nộp lần nào. Hãy đảm bảo nộp đúng hạn.</p>
              </div>
            <?php else: ?>
              <?php foreach ($submissions as $submission): ?>
                <article class="timeline-item">
                  <div class="timeline-item__indicator"></div>
                  <time
                    class="timeline-item__time text-xs"><?= date('d/m/Y H:i', strtotime($submission['submitted_at'])) ?></time>
                  <div class="timeline-item__title" title="<?= htmlspecialchars($submission['original_file_name'] ?? '') ?>">
                    <?php
                    $typeLabels = [
                      'internship_report' => 'Báo cáo TT',
                      'evaluation_form' => 'Phiếu đánh giá',
                      'company_survey' => 'Khảo sát DN',
                      'related_photo' => 'Hình ảnh khác'
                    ];
                    $docType = $submission['type'] ?? 'internship_report';
                    echo '[' . ($typeLabels[$docType] ?? 'Tài liệu') . '] ' . htmlspecialchars($submission['original_file_name'] ?? '--');
                    ?>
                  </div>
                  <div class="mt-2">
                    <?php
                    $downloadUrl = url('storage/' . ltrim($submission['file_path'], '/'));
                    if ($downloadUrl):
                      ?>
                      <a href="<?= $downloadUrl ?>" target="_blank" class="btn" data-variant="outline" data-size="sm"
                        title="Xem tài liệu">
                        <i class="fa-solid fa-eye mr-1"></i>Xem
                      </a>
                    <?php else: ?>
                      <span class="text-xs ml-2" title="File không tồn tại trên hệ thống">
                        <i class="fa-solid fa-circle-exclamation"></i> Lỗi file
                      </span>
                    <?php endif; ?>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
<?php else: ?>
  <div class="card shadow py-12 text-center">
    <div class="card__content empty">
      <div class="empty__header">
        <div class="empty__media"><i class="fa-solid fa-lock" aria-hidden="true"></i></div>
        <h3 class="empty__title">Đợt thực tập chưa được công bố</h3>
        <p class="empty__description">Vui lòng quay lại sau khi đợt thực tập được công bố.</p>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php $layout->start("scripts") ?>
<?php if (!$current && !empty($batches)): ?>
  <script type="application/json" data-tm-data="student_batches_table">
                          <?= json_encode([
                            'rows' => array_map(function ($batch) {
                                  $model = new InternshipBatch();
                                  $model->status = $batch['status'] ?? 'draft';
                                  $model->start_at = $batch['start_at'] ?? null;
                                  $model->end_at = $batch['end_at'] ?? null;
                                  $status = $model->getEffectiveStatus();

                                  return [
                                    'id' => $batch['id'],
                                    'title' => $batch['title'] ?? 'N/A',
                                    'start_at_label' => !empty($batch['start_at']) ? date('d/m/Y', strtotime($batch['start_at'])) : 'N/A',
                                    'end_at_label' => !empty($batch['end_at']) ? date('d/m/Y', strtotime($batch['end_at'])) : 'N/A',
                                    'effective_status' => $status,
                                    'effective_status_label' => BatchStatus::getLabel($status),
                                    'effective_status_variant' => BatchStatus::getVariant($status),
                                    '_href' => url('student/internship/' . $batch['id']),
                                    '_label' => 'Xem chi tiết đợt thực tập ' . ($batch['title'] ?? '')
                                  ];
                                }, $batches)
                          ]) ?>
                        </script>
  <script>
    (() => {
      const root = document.querySelector('[data-tm="student_batches_table"]');
      if (!root) return;

      const enhanceRows = () => {
        root.querySelectorAll('.tm-tbody .tm-tr').forEach((row) => {
          if (row.dataset.rowNavigationReady) return;
          const link = row.querySelector('a[href]');
          if (!link) return;

          row.dataset.rowNavigationReady = 'true';
          row.classList.add('tm-tr--interactive');
          row.tabIndex = 0;
          row.setAttribute('role', 'link');
          row.setAttribute('aria-label', `Xem chi tiết ${link.textContent.trim()}`);
          row.addEventListener('click', (event) => {
            if (event.target.closest('a, button, input, select, textarea')) return;
            window.location.href = link.href;
          });
          row.addEventListener('keydown', (event) => {
            if (event.target !== row || !['Enter', ' '].includes(event.key)) return;
            event.preventDefault();
            window.location.href = link.href;
          });
        });
      };

      root.addEventListener('tm:render', enhanceRows);
      document.addEventListener('DOMContentLoaded', enhanceRows);
    })();
  </script>
<?php endif; ?>
<script>
  window.API_BASE_URL = <?= json_encode(url('api/v1')) ?>;
</script>
<script src="<?= url('public/js/pages/student_dashboard.js') ?>"></script>
<?php $layout->end() ?>
