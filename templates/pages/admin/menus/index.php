<?php if ($flash = request()->session()->getFlash("notification")): ?>
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
          <?= $data->getTotal(); ?>
        </span>
      </h2>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/menus/create') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-plus"></i>
        Thêm
      </a>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<div class="table-wrapper shadow rounded-md">
  <table class="data-table">
    <thead>
      <tr>
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
      <?php if (!empty($data->getItems())): ?>
        <?php foreach ($data->getItems() as $index => $menu): ?>
          <tr onclick="window.location.href='<?= url('admin/menus/' . $menu->id) ?>'">
            <td><?= htmlspecialchars($menu->label ?? 'N/A') ?></td>
            <td>
              <code><?= htmlspecialchars($menu->key ?? 'N/A') ?></code>
            </td>
            <td>
              <?php if (!$menu->isEditable()): ?>
                <span class="badge" data-variant="primary">Hệ thống</span>
              <?php else: ?>
                <span class="badge" data-variant="secondary">Tuỳ chỉnh</span>
              <?php endif; ?>
            </td>
            <td><?= (int) ($menu->itemCount ?? 0) ?> mục</td>
            <td><?= htmlspecialchars($menu->description ?? 'N/A') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center">Không tìm thấy Menu nào.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?php
  $page = $data;
  include BASE_PATH . '/templates/components/pagination.php'
    ?>
</div>