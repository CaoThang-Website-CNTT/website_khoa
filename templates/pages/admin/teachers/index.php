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
        Giảng Viên
        <span class="badge" data-variant="primary">
          <?= (int) count($teachers ?? []) ?>
        </span>
      </h2>
    </div>

    <div class="col-6 col-md-6 flex gap-2">

      <div>
        <a href="<?= url('admin/teachers/create') ?>" data-variant="primary" data-size="md" class="btn">
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
          <h6>Email</h6>
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
      <?php if (!empty($teachers)): ?>
        <?php foreach ($teachers as $index => $user): ?>
          <tr onclick="window.location.href='<?= url('admin/teachers/' . $user->account_id) ?>'">
            <td class="data-table__id">#<?= $index + 1 ?></td>
            <td><?= htmlspecialchars($user->full_name ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($user->account->email ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($user->gender ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($user->dob ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($user->phone ?? 'N/A') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center">No teachers found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>