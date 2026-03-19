<?php
require_once __DIR__ . '/../../components/pagination.php';

use App\Components\Pagination;
// ── Build panels via output buffering ──────────────────────────────────────
ob_start(); ?>
<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">Classrooms</h2>
    </div>

    <!-- Action buttons — JS shows/hides the correct group on tab change -->
    <div class="col-6 col-md-6 flex gap-2">

      <div>
        <a href="<?= url('admin/students/create') ?>" data-variant="primary" data-size="md" class="btn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
            <path
              d="M256 64c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 160-160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l160 0 0 160c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160 160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-160 0 0-160z" />
          </svg>
          Thêm
        </a>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<div class="table-wrapper shadow rounded-md">
  <table class="data-table">
    <thead>
      <tr>
        <th></th>
        <th>
          <h6>Short name</h6>
        </th>
        <th>
          <h6>Level</h6>
        </th>
        <th>
          <h6>Class Of</h6>
        </th>
        <th>
          <h6>Profession</h6>
        </th>
        <th>
          <h6>Major</h6>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($classrooms)): ?>
      <?php foreach ($classrooms as $index => $classroom): ?>
      <tr onclick="window.location.href='<?= url('admin/classrooms/edit/' . $classroom->id) ?>'">
        <td class="data-table__id">#<?= $index + 1 ?></td>
        <td><?= htmlspecialchars($classroom->short_name ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($classroom->level ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($classroom->class_of ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($classroom->profession->full_name ?? 'N/A') ?></td>
        <td><?= htmlspecialchars($classroom->major->full_name ?? 'N/A') ?></td>
      </tr>
      <?php endforeach; ?>
      <?php else: ?>
      <tr>
        <td colspan="6" class="text-center">No classrooms found.</td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?= Pagination::render($currentPage, $classroomTotalPages, $classroomBaseUrl); ?>
</div>