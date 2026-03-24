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
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Menu
        <span class="badge" data-variant="primary">
          <?= (int) count($menus ?? []) ?>
        </span>
      </h2>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/menus/create') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-plus"></i>
        Tạo menu mới
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
          <h6>Tên menu</h6>
        </th>
        <th>
          <h6>Key</h6>
        </th>
        <th>
          <h6>Loại</h6>
        </th>
        <th>
          <h6>Số mục</h6>
        </th>
        <th>
          <h6>Mô tả</h6>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($menus)): ?>
        <?php foreach ($menus as $index => $menu): ?>
          <tr onclick="window.location.href='<?= url('admin/menus/' . $menu->id) ?>'">
            <td class="data-table__id">#<?= $index + 1 ?></td>
            <td><?= htmlspecialchars($menu->label ?? 'N/A') ?></td>
            <td>
              <code><?= htmlspecialchars($menu->key ?? 'N/A') ?></code>
            </td>
            <td>
              <?php if ($menu->isConst()): ?>
                <span class="badge" data-variant="primary">Hệ thống</span>
              <?php else: ?>
                <span class="badge" data-variant="secondary">Tuỳ chỉnh</span>
              <?php endif; ?>
            </td>
            <td><?= (int) ($menu->itemCount ?? 0) ?> mục</td>
            <td><?= htmlspecialchars($menu->description ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center">Chưa có menu nào.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?php include BASE_PATH . '/templates/components/pagination.php' ?>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    <?php foreach ($menus as $menu): ?>
      <?php if ($menu->isEditable()): ?>
        new Modal('#delete-modal-<?= $menu->id ?>');
      <?php endif; ?>
    <?php endforeach; ?>
  });
</script>