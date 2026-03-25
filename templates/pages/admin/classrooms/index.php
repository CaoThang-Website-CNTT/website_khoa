<!-- Toast khi redirect về đây có set flash (ví dụ: sau khi xóa thành công) -->
<?php if ($flash = request()->getFlash()): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= ($flash['type']) ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>
<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Lớp học
        <span class="badge" data-variant="primary">
          <?= (int) count($classrooms ?? []) ?>
        </span>
      </h2>
    </div>

    <div class="flex gap-2">
      <div>
        <a href="<?= url('admin/classrooms/create') ?>" data-variant="primary" data-size="md" class="btn">
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
          <h6>Name</h6>
        </th>
        <th>
          <h6>Major</h6>
        </th>
        <th>
          <h6>Specialization</h6>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($classrooms)): ?>
        <?php foreach ($classrooms as $index => $classroom): ?>
          <tr onclick="window.location.href='<?= url('admin/classrooms/' . $classroom->id) ?>'">
            <td class="data-table__id">#
              <?= htmlspecialchars($classroom->id ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($classroom->short_name ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($classroom->major->full_name ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($classroom->specialization->full_name ?? 'N/A') ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="4" class="text-center">Không tìm thấy lớp học nào.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?php include BASE_PATH . '/templates/components/pagination.php' ?>
</div>