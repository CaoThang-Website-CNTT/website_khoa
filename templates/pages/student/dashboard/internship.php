<?php

use App\Enums\BatchStatus;
use App\Models\InternshipBatch;

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

<!-- ========== title-wrapper start ========== -->
<link rel="stylesheet" href="<?= url('public/css/student_dashboard.css') ?>">
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div>
      <div class="flex items-center gap-2">
        <h1 class="title text-2xl font-semibold">Thông tin thực tập</h1>
        <?php if ($effectiveMetadata): ?>
          <span class="badge"
            data-variant="<?= $effectiveMetadata['variant'] ?>"><?= $effectiveMetadata['label'] ?></span>
        <?php endif; ?>
      </div>
      <p>Xem chi tiết đợt thực tập và kết quả đánh giá.</p>
    </div>

    <div class="flex items-center gap-4">
      <?php if ($current): ?>
        <button type="button" class="btn" data-variant="primary" data-size="lg" data-modal-trigger="#rl_requestModal">
          <i class="fa-solid fa-file-contract mr-2"></i>
          Đăng ký giấy giới thiệu
        </button>
        <div id="internship-data" data-batch-student-id="<?= $current['batch_student_id'] ?>" class="hidden"></div>
      <?php endif; ?>

      <div class="field">
        <select class="field__input" onchange="window.location.href = '<?= url('student/internship') ?>/' + this.value">
          <?php if (empty($batches)): ?>
            <option disabled selected>Chưa tham gia đợt nào</option>
          <?php else: ?>
            <?php foreach ($batches as $b): ?>
              <option value="<?= $b['id'] ?>" <?= $current && $current['id'] == $b['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars(mb_strimwidth($b['title'], 0, 25, "...")) ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<?php if ($current): ?>
  <div class="detail-layout">
    <div class="detail-layout__main">
      <!-- Chi tiết phân công -->
      <div class="card shadow">
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-circle-info mr-2"></i>
            Chi tiết phân công
          </h3>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div>
            <p><span class="font-bold">Đợt thực tập:</span> <?= htmlspecialchars($current['title'] ?? '') ?></p>
            <p><span class="font-bold">Thời gian mở đợt:</span> từ <time
                datetime="<?= date("d/m/Y", strtotime($current['start_at'])) ?>"><?= htmlspecialchars(date("d/m/Y", strtotime($current['start_at']))) ?></time>
              đến <time
                datetime="<?= date("d/m/Y", strtotime($current['end_at'])) ?>"><?= htmlspecialchars(date("d/m/Y", strtotime($current['end_at']))) ?>
            </p>


            <?php if ($supervisor): ?>
              <hr class="separator" />
              <p><span class="font-bold">Họ & tên GVHD:</span> <?= htmlspecialchars($supervisor->full_name) ?></p>
              <p><span class="font-bold">Email GVHD:</span> <?= htmlspecialchars($supervisor->account->email) ?></p>
              <p><span class="font-bold">Số điện thoại GVHD:</span> <?= htmlspecialchars($supervisor->phone) ?></p>
            <?php else: ?>
              <p><span class="font-bold">Giảng viên hướng dẫn:</span> Chưa phân công</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Khai báo công ty -->
      <div class="card shadow">
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-building mr-2"></i>
            Thông tin công ty
          </h3>
          <div class="card__header-meta">
            <?php if ($company_deadline): ?>
              <?php
              $deadlineDt = new \DateTime($company_deadline);
              $now = new \DateTime();
              $isNear = $now >= (clone $deadlineDt)->modify("-{$company_warning_days} days") && $now <= $deadlineDt;
              $isPassed = $now > $deadlineDt;
              ?>
              <?php if ($isPassed): ?>
                <span class="badge" data-variant="destructive">
                  <i class="fa-solid fa-lock mr-1"></i> Đã hết hạn
                </span>
              <?php elseif ($isNear): ?>
                <span class="badge" data-variant="warning">
                  <i class="fa-solid fa-triangle-exclamation mr-1"></i> Sắp hết hạn
                </span>
              <?php endif; ?>
              <p class="text-xs">Hạn chót khai báo: <span class="font-semibold"><?= $deadlineDt->format('d/m/Y') ?></span>
              </p>
            <?php endif; ?>
          </div>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <?php if ($can_edit_company): ?>
            <form action="<?= url("student/internship/{$current['id']}/company") ?>" method="POST" id="companyForm">
              <?= csrf_field() ?>
              <input type="hidden" name="batch_student_id" value="<?= $current['batch_student_id'] ?>">

              <div class="field mb-3" data-orientation="horizontal">
                <input type="checkbox" id="is_manual" name="is_manual" value="1" class="field__input">
                <label for="is_manual" class="field__label">Tôi không tìm thấy mã số thuế / Công ty không có mã số
                  thuế</label>
              </div>

              <div class="field mb-3" data-field-required>
                <label class="field__label">Mã số thuế</label>
                <div class="field__input-group">
                  <input type="text" name="tax_code" id="tax_code" class="field__input" required
                    value="<?= htmlspecialchars($current['company_tax_code'] ?? '') ?>">
                  <button type="button" id="btnCheckMST" data-variant="outline" data-size="md" class="btn">Kiểm tra</button>
                </div>
                <div id="mstLoading" class="field__description hidden"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải
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

              <div class="grid grid-cols-2 gap-3 mb-4">
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

              <div class="flex justify-end mt-2">
                <button type="submit" class="btn" data-variant="primary" data-size="lg">Lưu thông tin</button>
              </div>
            </form>
          <?php else: ?>
            <?php if ($current['company_name']): ?>
              <div class="grid gap-2">
                <p><span class="font-bold">Tên công ty:</span> <?= htmlspecialchars($current['company_name'] ?? 'Chưa có') ?>
                </p>
                <p><span class="font-bold">MST:</span> <?= htmlspecialchars($current['company_tax_code'] ?? 'Chưa có') ?></p>
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
              <div class="empty-state">
                <p class="text-sm" style="color: var(--muted-foreground);">Chưa có thông tin công ty và đã hết thời gian khai báo.
                </p>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Giấy giới thiệu -->
      <div class="card shadow">
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-file-contract mr-2"></i>
            Giấy giới thiệu
          </h3>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <?php if (empty($recent_referral_letters)): ?>
            <p class="text-sm text-center">Chưa đăng ký giấy nào.</p>
          <?php else: ?>
            <div class="space-y-3">
              <?php foreach ($recent_referral_letters as $rl): ?>
                <div class="border rounded p-3 text-sm">
                  <div class="font-bold mb-1" title="<?= htmlspecialchars($rl['company_name']) ?>">
                    <?= htmlspecialchars($rl['company_name']) ?></div>
                  <div class="flex justify-between items-center mt-2">
                    <span class="text-xs"><?= date('d/m/Y', strtotime($rl['created_at'])) ?></span>
                    <?php if ($rl['status'] === 'pending'): ?>
                      <span class="badge" data-variant="secondary">Đang xử lý</span>
                    <?php elseif ($rl['status'] === 'printed'): ?>
                      <span class="badge" data-variant="primary">Đã in</span>
                    <?php else: ?>
                      <span class="badge" data-variant="destructive">Đã hủy</span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <?php if (!empty($recent_referral_letters)): ?>
          <div class="card__footer">
            <a href="<?= url("student/internship/{$current['id']}/referral_letters") ?>" class="btn w-full"
              data-variant="outline" data-size="sm">
              Xem tất cả (<?= $total_referral_letters ?>)
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="detail-layout__sidebar">
      <!-- Kết quả -->
      <div class="card shadow result-card">
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-star mr-2"></i>
            Kết quả thực tập
          </h3>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div>

            <?php if ($grade && isset($grade['final_score'])): ?>
              <div class="font-bold text-4xl text-center">
                <?= $grade['final_score'] ?>
              </div>

              <?php if (!empty($grade['score_reason'])): ?>
                <hr class="separator" />
                <p class="text-sm">
                  <span class="font-medium" style="color: var(--muted-foreground);">Chi tiết điểm:</span>
                  <?= nl2br(htmlspecialchars($grade['score_reason'])) ?>
                </p>
              <?php endif; ?>

              <?php if (!empty($grade['feedback'])): ?>
                <hr class="separator" />
                <div class="text-sm">
                  <span class="font-medium" style="color: var(--muted-foreground);">Nhận xét của GV:</span>
                  <?= nl2br(htmlspecialchars($grade['feedback'])) ?>
                </div>
              <?php endif; ?>

            <?php else: ?>
              <span style="color: var(--muted-foreground);">Chưa có điểm</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Nộp tài liệu -->
      <?php if ($effStatus === BatchStatus::ACTIVE): ?>
        <div class="card shadow">
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

              <div class="field mb-3" data-field-required>
                <label class="field__label">Loại tài liệu</label>
                <select name="doc_type" id="doc_type" class="field__input" required>
                  <option value="" disabled selected>-- Chọn loại tài liệu --</option>
                  <option value="internship_report">Báo cáo thực tập tốt nghiệp (Bắt buộc)</option>
                  <option value="evaluation_form">Phiếu đánh giá thực tập (Bắt buộc)</option>
                  <option value="company_survey">Phiếu khảo sát doanh nghiệp</option>
                  <option value="related_photo">Hình ảnh liên quan khác</option>
                </select>
              </div>

              <div class="upload-area" id="uploadArea">
                <div class="upload-area__icon">
                  <i class="fa-solid fa-file-arrow-up"></i>
                </div>
                <p class="upload-area__text">Nhấn để chọn file hoặc kéo thả vào đây</p>
                <p class="upload-area__hint">Gồm: báo cáo thực tập, phiếu đánh giá, nhận xét của công ty, hình ảnh liên quan.</p>
                <p class="upload-area__hint">Định dạng hỗ trợ: PDF (cho Báo cáo, phiếu đánh giá, khảo sát), Hình ảnh (JPG, PNG, WEBP cho Hình ảnh liên quan). Dung lượng tối đa: <?= $max_file_size_mb ?>MB</p>
                <input type="file" name="report_file" class="hidden" id="report_file" accept=".pdf,image/jpeg,image/png,image/webp">
              </div>
              <div id="filePreview" class="hidden mt-4 text-sm text-center"></div>
              <div class="mt-4 flex justify-end">
                <?php if ($can_submit_report): ?>
                  <button type="submit" class="btn" data-variant="primary" data-size="lg" disabled id="uploadBtn">Nộp tài
                    liệu</button>
                <?php else: ?>
                  <button type="button" class="btn" data-variant="outline" data-size="lg" disabled>Đã hết hạn nộp</button>
                <?php endif; ?>
              </div>
            </form>
          </div>
          <hr class="separator" />
          <div class="card__header">
            <h3 class="card__title">
              <i class="fa-solid fa-upload mr-2"></i>
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
                      $downloadUrl = url('/public/media/' . $submission['file_path']);
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
      <?php endif; ?>

      <!-- Thông báo & Lịch sử -->
      <div class="card shadow">
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-clock-rotate-left mr-2"></i>
            Thông báo & Lịch sử
          </h3>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="timeline-container">
            <?php if (empty($logs)): ?>
              <div class="empty-state">
                <p class="text-sm">Chưa có lịch sử hoạt động.</p>
              </div>
            <?php else: ?>
              <?php foreach ($logs as $log): ?>
                <article class="timeline-item">
                  <div class="timeline-item__indicator"></div>
                  <time class="timeline-item__time text-xs"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></time>
                  <div class="timeline-item__title"><?= htmlspecialchars($log['action']) ?></div>
                  <?php if ($log['reason']): ?>
                    <p class="timeline-item__reason text-sm mt-1"><?= htmlspecialchars($log['reason']) ?></p>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php else: ?>
  <div class="card shadow py-12 text-center">
    <div class="card__content flex flex-col items-center">
      <div class="text-5xl mb-6">
        <i class="fa-solid fa-calendar-xmark"></i>
      </div>
      <h2 class="text-xl font-semibold mb-2">Chưa tham gia đợt thực tập nào</h2>
      <p class="max-w-sm mx-auto">Thông tin thực tập sẽ hiển thị sau khi bạn được đưa vào danh sách đợt thực tập mới.</p>
    </div>
  </div>
<?php endif; ?>

<script>
  window.API_BASE_URL = '<?= url('api/v1') ?>';
</script>
<script src="<?= url('public/js/pages/student_dashboard.js') ?>"></script>

<?php if ($current): ?>
  <!-- Modal Đăng ký Giấy giới thiệu -->
  <div id="rl_requestModal" class="modal" data-state="closed">
    <div class="modal__content modal--lg">
      <div class="modal__header">
        <h3 class="modal__title">Đăng ký giấy giới thiệu thực tập</h3>
        <button class="modal__close" data-modal-close="rl_requestModal">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="modal__body">
        <form action="<?= url("student/internship/{$current['id']}/referral_letters") ?>" method="POST"
          id="rl_requestForm">
          <?= csrf_field() ?>

          <div class="field mb-4" data-orientation="horizontal">
            <input type="checkbox" id="rl_is_manual" name="is_manual" value="1" class="field__input">
            <label for="rl_is_manual" class="field__label">Tôi không tìm thấy mã số thuế / Công ty không có mã số
              thuế</label>
          </div>

          <div class="field mb-4" data-field-required>
            <label class="field__label">Mã số thuế</label>
            <div class="field__input-group">
              <input type="text" name="tax_code" id="rl_tax_code" class="field__input" required>
              <button type="button" id="rl_btnCheckMST" data-variant="outline" data-size="md" class="btn">Kiểm
                tra</button>
            </div>
            <div id="rl_mstLoading" class="field__description hidden"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải
              thông tin...</div>
            <div id="rl_mstError" class="field__error hidden"></div>
          </div>

          <div class="field mb-4" data-field-required>
            <label class="field__label">Tên công ty</label>
            <div class="field__suggest-wrapper">
              <input type="text" name="name" id="rl_company_name" class="field__input relative" required readonly
                autocomplete="off">
              <div id="rl_companySuggestions" class="suggestions-list hidden"></div>
            </div>
          </div>

          <div class="field mb-4" data-field-required>
            <label class="field__label">Địa chỉ</label>
            <textarea name="address" id="rl_company_address" class="field__input" required readonly></textarea>
          </div>
        </form>
      </div>
      <div class="modal__footer">
        <button type="button" class="btn" data-variant="outline" data-modal-close="rl_requestModal">Hủy</button>
        <button type="submit" form="rl_requestForm" class="btn" data-variant="primary">Gửi đăng ký</button>
      </div>
    </div>
  </div>
<?php endif; ?>