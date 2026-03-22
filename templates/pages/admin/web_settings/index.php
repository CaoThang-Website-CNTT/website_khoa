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
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
          <path
            d="M256 64c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 160-160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l160 0 0 160c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160 160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-160 0 0-160z" />
        </svg>
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
        <?php $index = 1;
        foreach ($groups as $groupName => $settings): ?>
          <?php
          $total = count($settings);
          $autoloaded = count(array_filter($settings, fn($s) => $s->autoload));
          $locked = count(array_filter($settings, fn($s) => $s->is_locked));
          ?>
          <tr onclick="window.location.href='<?= url('admin/web_settings/' . $groupName . '/edit') ?>'">
            <td class="data-table__id">#<?= $index++ ?></td>
            <td>
              <code><?= htmlspecialchars($groupName) ?></code>
            </td>
            <td><?= $total ?></td>
            <td>
              <?php if ($locked > 0): ?>
                <span class="badge" data-variant="primary"><?= $locked ?> khoá</span>
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
</div>