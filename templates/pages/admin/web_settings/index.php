<?php if ($flash = request()->getFlash()): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= $flash['type'] ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div>
      <h2 class="title text-2xl font-semibold">
        Cài đặt hệ thống
        <span class="badge" data-variant="primary">
          <?= count($groups) ?>
        </span>
      </h2>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/web_settings/create') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-plus"></i>
        Thêm setting
      </a>
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
          <h6>Nhóm</h6>
        </th>
        <th>
          <h6>Số cài đặt</h6>
        </th>
        <th>
          <h6>Cài đặt hệ thống</h6>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($groups)): ?>
        <?php
        foreach ($groups as $index => $group): ?>
          <?php
          ?>
          <tr onclick="window.location.href='<?= url('admin/web_settings/' . $group['name'] . '/edit') ?>'">
            <td class="data-table__id">#<?= $index++ ?></td>
            <td>
              <code><?= htmlspecialchars($group['name']) ?></code>
            </td>
            <td><?= $group['total'] ?></td>
            <td>
              <?php if ($group['is_locked'] > 0): ?>
                <span class="badge" data-variant="primary"><?= $group['is_locked'] ?> khoá</span>
              <?php else: ?>
                <span class="badge" data-variant="secondary">Không có</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center">Chưa có cài đặt nào.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?php include BASE_PATH . '/templates/components/pagination.php' ?>
</div>