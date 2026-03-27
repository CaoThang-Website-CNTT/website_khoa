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
        Categories
        <span class="badge" data-variant="primary">
          <?= $data->getTotal(); ?>
        </span>
      </h2>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/categories/create') ?>" data-variant="primary" data-size="md" class="btn">
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
          <h6>Name</h6>
        </th>
        <th>
          <h6>Slug</h6>
        </th>
        <th>
          <h6>Type</h6>
        </th>
        <th>
          <h6>Description</h6>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($data->getItems())): ?>
        <?php foreach ($data->getItems() as $category): ?>
          <tr onclick="window.location.href='<?= url('admin/categories/' . $category->id) ?>'">
            <td>
              <?= htmlspecialchars($category->name) ?>
            </td>
            <td><?= htmlspecialchars($category->slug ?? 'N/A') ?></td>
            <td>
              <?php if ($category->type === 'const'): ?>
                <span class="badge" data-variant="primary">Hệ thống</span>
              <?php else: ?>
                <span class="badge" data-variant="secondary">Tùy chỉnh</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!isset($category->parent_id)): ?>
                <span class="badge" data-variant="primary">Cha</span>
              <?php else: ?>
                <span class="badge" data-variant="secondary">Con</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($category->description ?? 'N/A') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center">Không tìm thấy Danh Mục nào.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?php
  $page = $data;
  include BASE_PATH . '/templates/components/pagination.php'
    ?>
</div>