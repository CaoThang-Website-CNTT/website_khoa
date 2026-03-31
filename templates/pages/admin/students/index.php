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
        Sinh Viên
        <span class="badge" data-variant="primary">
          <?= $data->getTotal() ?>
        </span>
      </h2>
    </div>

    <div class="flex gap-2">
      <div>
        <a href="<?= url('admin/students/create') ?>" data-variant="primary" data-size="md" class="btn">
          <i class="fa-solid fa-plus"></i>
          Thêm
        </a>
        <a href="<?= url('admin/students/import') ?>" data-variant="outline" data-size="md" class="btn">
          <i class="fa-solid fa-file-import"></i>
          Nhập
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
          <h6>MSSV</h6>
        </th>
        <th>
          <h6>Gender</h6>
        </th>
        <th>
          <h6>Date of Birth</h6>
        </th>
        <th>
          <h6>Phone</h6>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($data->getItems())): ?>
        <?php foreach ($data->getItems() as $index => $student): ?>
          <tr onclick="window.location.href='<?= url('admin/students/' . $student->student_id) ?>'">
            <td class="data-table__id">
              <?= '#' . htmlspecialchars($student->id ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($student->full_name ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($student->student_id ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($student->gender ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($student->dob ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($student->phone ?? 'N/A') ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center">Không tìm thấy Sinh Viên nào.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?php
  $page = $data;
  include BASE_PATH . '/templates/components/pagination.php'
    ?>
</div>