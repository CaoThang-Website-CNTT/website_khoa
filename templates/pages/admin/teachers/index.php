<!-- Toast khi redirect về đây có set flash (ví dụ: sau khi xóa thành công) -->
<?php if ($flash = request()->session()->getFlash("notification")): ?>
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
        Giảng Viên
        <span class="badge" data-variant="primary">
          <?= $data->getTotal() ?>
        </span>
      </h2>
    </div>

    <div class="flex gap-2">

      <div>
        <a href="<?= url('admin/teachers/create') ?>" data-variant="primary" data-size="md" class="btn">
          <i class="fa-solid fa-plus"></i>
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
          <h6>Mã GV</h6>
        </th>
        <th>
          <h6>Gender</h6>
        </th>
        <th>
          <h6>Date of Bỉrth</h6>
        </th>
        <th>
          <h6>Positon</h6>
        </th>
        <th>
          <h6>Department</h6>
        </th>
        <th>
          <h6>Contract</h6>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($data->getItems())): ?>
        <?php foreach ($data->getItems() as $index => $teacher): ?>
          <tr onclick="window.location.href='<?= url('admin/teachers/' . $teacher->id) ?>'">
            <td class="data-table__id">
              <?= '#' . htmlspecialchars($teacher->id ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($teacher->full_name ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($teacher->staff_code ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($teacher->gender ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($teacher->dob ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($teacher->position ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($teacher->department ?? 'N/A') ?>
            </td>
            <td>
              <span class="badge" data-variant="primary">
                <?= htmlspecialchars($teacher->contract_type ?? 'N/A') ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center">Không tìm thấy Giảng Viên nào.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?php
  $page = $data;
  include BASE_PATH . '/templates/components/pagination.php'
    ?>
</div>