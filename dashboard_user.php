<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">Users</h2>
    </div>
    <div class="col-6 col-md-6 flex gap-2">
      <a href="<?= url('admin/students/create') ?>" data-variant="primary" data-size="md" class="btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
          <path
            d="M256 64c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 160-160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l160 0 0 160c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160 160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-160 0 0-160z" />
        </svg>
        Thêm
      </a>
      <a href="user_import.php" data-variant="outline" data-size="md" class="btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
          <path
            d="M64 0C28.7 0 0 28.7 0 64l0 240 182.1 0-31-31c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l72 72c9.4 9.4 9.4 24.6 0 33.9l-72 72c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l31-31-182.1 0 0 96c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-277.5c0-17-6.7-33.3-18.7-45.3L258.7 18.7C246.7 6.7 230.5 0 213.5 0L64 0zM325.5 176L232 176c-13.3 0-24-10.7-24-24L208 58.5 325.5 176z" />
        </svg>
        Nhập
      </a>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<!-- ========== table-wrapper start ========== -->
<div class="table-wrapper shadow rounded-md">
  <table class="data-table">
    <thead>
      <tr>
        <th>
          <!-- Index Dummy Placeholder -->
        </th>
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
      <?php if (!empty($students)): ?>
        <?php foreach ($students as $index => $user): ?>
          <tr onclick="window.location.href='<?= url('admin/students/edit/' . $user->account_id) ?>'">
            <td class="data-table__id">#<?= $index + 1; ?></td>
            <td><?= htmlspecialchars($user->fullname ?? 'N/A'); ?></td>
            <td><?= htmlspecialchars($user->account->email ?? 'N/A'); ?></td>
            <td><?= htmlspecialchars($user->gender ?? 'N/A'); ?></td>
            <td><?= htmlspecialchars($user->dob ?? 'N/A'); ?></td>
            <td><?= htmlspecialchars($user->phone ?? 'N/A'); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center">No students found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<!-- ========== table-wrapper end ========== -->