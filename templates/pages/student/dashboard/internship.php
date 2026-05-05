<?php
/**
 * View: Thông tin thực tập sinh viên
 * Route: /student/internship
 * SEO: Thông tin chi tiết về đợt thực tập của sinh viên
 */
$student = $student ?? null;
$current = $current ?? null;
$batches = $batches ?? [];
$supervisor = $supervisor ?? null;
$logs = $logs ?? [];
?>

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div>
      <h1 class="title text-2xl font-semibold">Thông tin thực tập</h1>
      <p>Xem chi tiết đợt thực tập và kết quả đánh giá.</p>
    </div>

    <div class="flex items-center gap-4">
      <?php if ($current): ?>
        <div id="internship-data" data-batch-student-id="<?= $current['batch_student_id'] ?>" class="hidden"></div>
      <?php endif; ?>

      <div class="field">
        <select class="field__input" onchange="window.location.href = '?batch_id=' + this.value">
          <?php if (empty($batches)): ?>
            <option disabled selected>Chưa tham gia đợt nào</option>
          <?php else: ?>
            <?php foreach ($batches as $b): ?>
              <option value="<?= $b['id'] ?>" <?= $current && $current['id'] == $b['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($b['title']) ?>
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
            <i class="fa-solid fa-circle-info text-primary mr-2"></i>
            Chi tiết phân công
          </h3>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">
            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-readonly>
                <label class="field__label">Đợt thực tập</label>
                <input class="field__input" type="text" readonly value="<?= htmlspecialchars($current['title'] ?? '') ?>">
              </div>
              <div class="field" data-field-readonly>
                <label class="field__label">Trạng thái</label>
                <?php
                $statusVariant = match ($current['student_status'] ?? '') {
                  'pending' => 'warning',
                  'approved' => 'success',
                  'rejected' => 'destructive',
                  default => 'primary'
                };
                $statusText = match ($current['student_status'] ?? '') {
                  'pending' => 'Chờ duyệt',
                  'approved' => 'Đang thực tập',
                  'rejected' => 'Bị từ chối',
                  default => $current['student_status'] ?? 'N/A'
                };
                ?>
                <div class="flex items-center h-8">
                   <span class="badge" data-variant="<?= $statusVariant ?>"><?= $statusText ?></span>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-readonly>
                <label class="field__label">Giảng viên hướng dẫn</label>
                <input class="field__input" type="text" readonly value="<?= $supervisor ? htmlspecialchars($supervisor->full_name) : 'Chưa phân công' ?>">
              </div>
              <div class="field" data-field-readonly>
                <label class="field__label">Số điện thoại GVHD</label>
                <input class="field__input" type="text" readonly value="<?= $supervisor ? htmlspecialchars($supervisor->phone) : '--' ?>">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Nộp tài liệu -->
      <div class="card shadow">
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-cloud-arrow-up text-primary mr-2"></i>
            Nộp tài liệu tốt nghiệp
          </h3>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="upload-area" id="uploadArea">
            <div class="upload-area__icon">
              <i class="fa-solid fa-file-arrow-up"></i>
            </div>
            <p class="upload-area__text">Nhấn để chọn file hoặc kéo thả vào đây</p>
            <p class="upload-area__hint">Định dạng hỗ trợ: PDF, DOCX, ZIP. Dung lượng tối đa: 30MB</p>
          </div>
        </div>
      </div>

      <!-- Thông báo & Lịch sử -->
      <div class="card shadow">
        <div class="card__header">
          <h3 class="card__title">
            <i class="fa-solid fa-clock-rotate-left text-primary mr-2"></i>
            Thông báo & Lịch sử
          </h3>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="log-timeline">
            <?php if (empty($logs)): ?>
              <div class="empty-state">
                <p class="text-muted-foreground text-sm">Chưa có lịch sử hoạt động.</p>
              </div>
            <?php else: ?>
              <?php foreach ($logs as $log): ?>
                <article class="log-item">
                  <div class="log-item__indicator"></div>
                  <time class="log-item__time text-xs text-muted-foreground"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></time>
                  <div class="log-item__action font-medium"><?= htmlspecialchars($log['action']) ?></div>
                  <?php if ($log['reason']): ?>
                    <p class="log-item__reason text-sm text-muted-foreground mt-1"><?= htmlspecialchars($log['reason']) ?></p>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="detail-layout__sidebar">
      <!-- Kết quả -->
      <div class="card shadow result-card text-center py-8">
        <div class="card__header flex-col items-center gap-2">
          <h3 class="card__title text-sm uppercase tracking-wider text-muted-foreground">
            <i class="fa-solid fa-star text-warning mr-1"></i>
            Kết quả
          </h3>
        </div>
        <div class="card__content">
          <div class="text-4xl font-bold text-primary mb-1">--</div>
          <span class="text-xs text-muted-foreground">Chưa có điểm</span>
        </div>
      </div>
    </div>
  </div>
<?php else: ?>
  <div class="card shadow py-12 text-center">
    <div class="card__content flex flex-col items-center">
      <div class="text-5xl text-muted-foreground/20 mb-6">
        <i class="fa-solid fa-calendar-xmark"></i>
      </div>
      <h2 class="text-xl font-semibold mb-2">Chưa tham gia đợt thực tập nào</h2>
      <p class="text-muted-foreground max-w-sm mx-auto">Thông tin thực tập sẽ hiển thị sau khi bạn được đưa vào danh sách đợt thực tập mới.</p>
    </div>
  </div>
<?php endif; ?>

<script src="<?= url('public/js/pages/student_dashboard.js') ?>"></script>
