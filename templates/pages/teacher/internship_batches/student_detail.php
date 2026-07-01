<?php

/**
 * View: Chi tiết sinh viên 
 * Route: /teacher/internship_batches/{batchId}/student/{batchStudentId}
 */

$batchId = $batch['id'] ?? null;
$student = $student ?? [];
?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Chi tiết sinh viên: <?= htmlspecialchars($student['full_name'] ?? '') ?>
</h2>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url("teacher/internship_batches/{$batchId}") ?>" data-variant="outline" data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<?php $layout->end() ?>

<div class="detail-layout">

  <div class="detail-layout__main flex-1">
    <!-- Thông tin cá nhân -->
    <div class="card shadow">
      <div class="card__header">
        <h3 class="card__title">Thông tin sinh viên</h3>
      </div>
      <hr class="separator" />
      <div class="card__content">
        <div class="mb-4">
          <label class="block font-semibold">Họ và tên</label>
          <div class="mt-1"><?= htmlspecialchars($student['full_name'] ?? 'Chưa có') ?></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold">Mã số sinh viên</label>
          <div class="mt-1"><?= htmlspecialchars($student['student_code'] ?? 'Chưa có') ?></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold">Lớp</label>
          <div class="mt-1"><?= htmlspecialchars($student['classroom_name'] ?? 'Chưa có') ?></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold">Số điện thoại</label>
          <div class="mt-1"><?= htmlspecialchars($student['phone'] ?? 'Chưa có') ?></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold">Email</label>
          <div class="mt-1">
            <?php if (!empty($student['email'])): ?>
              <a href="mailto:<?= htmlspecialchars($student['email']) ?>"><?= htmlspecialchars($student['email']) ?></a>
            <?php else: ?>
              Chưa có
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="detail-layout__sidebar">
    <!-- Thông tin công ty thực tập -->
    <div class="card shadow">
      <div class="card__header">
        <h3 class="card__title">Thông tin thực tập</h3>
      </div>
      <hr class="separator" />
      <div class="card__content">
        <?php if (!empty($student['company_name'])): ?>
          <div class="mb-4">
            <label class="block font-semibold">Công ty thực tập</label>
            <div class="mt-1"><?= htmlspecialchars($student['company_name']) ?></div>
          </div>
          <div class="mb-4">
            <label class="block font-semibold">Địa chỉ công ty</label>
            <div class="mt-1"><?= htmlspecialchars($student['company_address'] ?? 'Chưa có') ?></div>
          </div>
          <div class="mb-4">
            <label class="block font-semibold">Vị trí thực tập</label>
            <div class="mt-1"><?= htmlspecialchars($student['position'] ?? 'Chưa có') ?></div>
          </div>
          <div class="mb-4">
            <label class="block font-semibold">Thời gian thực tập</label>
            <div class="mt-1">
              <?php
              $start = !empty($student['internship_start_date']) ? date('d/m/Y', strtotime($student['internship_start_date'])) : 'Chưa rõ';
              $end = !empty($student['internship_end_date']) ? date('d/m/Y', strtotime($student['internship_end_date'])) : 'Chưa rõ';
              echo "{$start} - {$end}";
              ?>
            </div>
          </div>
        <?php else: ?>
          <div class="p-6 text-center rounded-lg border">
            <i class="fa-solid fa-building text-3xl mb-3"></i>
            <p class="font-medium">Chưa có thông tin công ty</p>
            <p class="text-sm mt-1">Sinh viên chưa khai báo đơn vị thực tập hoặc chưa được duyệt giấy giới thiệu.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>