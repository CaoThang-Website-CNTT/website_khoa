<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">Categories</h2>
      <?php
      include BASE_PATH . '/templates/components/flash_alert.php';
      ?>
    </div>
    <div class="col-6 col-md-6 flex gap-2">
      <a href="<?= url('admin/categories/create') ?>" data-variant="primary" data-size="md" class="btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
          <path
            d="M256 64c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 160-160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l160 0 0 160c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160 160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-160 0 0-160z" />
        </svg>
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
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $category): ?>
          <tr onclick="window.location.href='<?= url('admin/categories/' . $category->id) ?>'">
            <td>
              <?php
              $parent = $category->parent_id;
              $indentClass = 'ml-' . $category->depth * 4;
              ?>
              <span class="<?= $indentClass ?>">
                <?= htmlspecialchars($category->name) ?>
              </span>
            </td>
            <td><?= htmlspecialchars($category->slug ?? 'N/A') ?></td>
            <td>
              <?php if ($category->type === 'const'): ?>
                <span class="badge" data-variant="primary">Hệ thống</span>
              <?php else: ?>
                <span class="badge" data-variant="secondary">Tùy chỉnh</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($category->description ?? 'N/A') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center">No categories found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>