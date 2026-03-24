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

<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div>
      <h2 class="title text-2xl font-semibold">
        Quản lý Carousel
        <span class="badge" data-variant="primary">
          <?= (int) count($carousels ?? []) ?>
        </span>
      </h2>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/carousels/create') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-plus"></i>
        Thêm
      </a>
    </div>
  </div>
</div>
<div class="table-wrapper">
  <table class="data-table">
    <thead>
      <tr>
        <th>
          <h6>ID</h6>
        </th>
        <th>
          <h6>Tên Carousel</h6>
        </th>
        <th>
          <h6>Slug</h6>
        </th>
        <th>
          <h6>Trạng thái</h6>
        </th>
        <th>
          <h6>Ngày tạo</h6>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($carousels)): ?>
        <?php foreach ($carousels as $carousel): ?>
          <tr onclick="window.location.href='<?= url('admin/carousels/' . $carousel->id) ?>'"
            class="cursor-pointer hover:bg-slate-50">
            <td class="data-table__id">#<?= $carousel->id ?></td>
            <td class="font-medium"><?= htmlspecialchars($carousel->name ?? 'N/A') ?></td>
            <td>
              <code><?= htmlspecialchars($carousel->slug ?? 'N/A') ?></code>
            </td>
            <td>
              <?php if ($carousel->isActive()): ?>
                <span class="badge" data-variant="success">Đang hoạt động</span>
              <?php else: ?>
                <span class="badge" data-variant="secondary">Đã ẩn</span>
              <?php endif; ?>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($carousel->created_at)) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center py-4">Chưa có carousel nào.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>