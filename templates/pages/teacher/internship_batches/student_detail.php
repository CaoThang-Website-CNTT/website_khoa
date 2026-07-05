<?php

/**
 * View: Chi tiết sinh viên 
 * Route: /teacher/internship_batches/{batchId}/student/{batchStudentId}
 */

$batchId = $batch['id'] ?? null;
$student = $student ?? [];
?>

<link rel="stylesheet" href="<?= url('public/css/teacher_student_detail.css') ?>">

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">Chi tiết sinh viên</h2>
<div class="title-wrapper__description student-detail__heading-meta">
  <span><?= htmlspecialchars($student['full_name'] ?? 'Chưa có') ?></span>
  <span aria-hidden="true">•</span>
  <span><?= htmlspecialchars($student['student_code'] ?? 'Chưa có') ?></span>
</div>
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
      <div class="card__content student-detail__rows">
        <dl class="student-detail__row">
          <dt>Họ và tên</dt>
          <dd><?= htmlspecialchars($student['full_name'] ?? 'Chưa có') ?></dd>
        </dl>
        <hr class="separator">
        <dl class="student-detail__row">
          <dt>Mã số sinh viên</dt>
          <dd><?= htmlspecialchars($student['student_code'] ?? 'Chưa có') ?></dd>
        </dl>
        <hr class="separator">
        <dl class="student-detail__row">
          <dt>Lớp</dt>
          <dd><?= htmlspecialchars($student['classroom_name'] ?? 'Chưa có') ?></dd>
        </dl>
        <hr class="separator">
        <dl class="student-detail__row">
          <dt>Số điện thoại</dt>
          <dd>
            <?php if (!empty($student['phone'])): ?>
              <a href="tel:<?= htmlspecialchars($student['phone']) ?>"><?= htmlspecialchars($student['phone']) ?></a>
            <?php else: ?>
              Chưa có
            <?php endif; ?>
          </dd>
        </dl>
        <hr class="separator">
        <dl class="student-detail__row">
          <dt>Email</dt>
          <dd>
            <?php if (!empty($student['email'])): ?>
              <a href="mailto:<?= htmlspecialchars($student['email']) ?>"><?= htmlspecialchars($student['email']) ?></a>
            <?php else: ?>
              Chưa có
            <?php endif; ?>
          </dd>
        </dl>
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
          <div class="student-detail__rows">
            <dl class="student-detail__row">
              <dt>Công ty thực tập</dt>
              <dd><?= htmlspecialchars($student['company_name']) ?></dd>
            </dl>
            <hr class="separator">
            <dl class="student-detail__row">
              <dt>Địa chỉ công ty</dt>
              <dd><?= htmlspecialchars($student['company_address'] ?? 'Chưa có') ?></dd>
            </dl>
            <hr class="separator">
            <dl class="student-detail__row">
              <dt>Vị trí thực tập</dt>
              <dd><?= htmlspecialchars($student['position'] ?? 'Chưa có') ?></dd>
            </dl>
            <hr class="separator">
            <dl class="student-detail__row">
              <dt>Thời gian thực tập</dt>
              <dd>
              <?php
              $start = !empty($student['internship_start_date']) ? date('d/m/Y', strtotime($student['internship_start_date'])) : 'Chưa rõ';
              $end = !empty($student['internship_end_date']) ? date('d/m/Y', strtotime($student['internship_end_date'])) : 'Chưa rõ';
              echo "{$start} - {$end}";
              ?>
              </dd>
            </dl>
          </div>
        <?php else: ?>
          <div class="student-detail__empty p-6 text-center rounded-lg border">
            <i class="fa-solid fa-building text-3xl mb-3"></i>
            <p class="font-medium">Chưa có thông tin công ty</p>
            <p class="text-sm mt-1">Sinh viên chưa khai báo đơn vị thực tập hoặc chưa được duyệt giấy giới thiệu.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
