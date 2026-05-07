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
        Công ty
        <span class="badge" data-variant="primary">
          <?= $data->count(); ?>
        </span>
      </h2>
    </div>

    <div class="flex gap-2">
      <div>
        <a href="<?= url('admin/companies/create') ?>" data-variant="primary" data-size="md" class="btn">
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
          <h6>Tên</h6>
        </th>
        <th>
          <h6>Mã số thuế</h6>
        </th>
        <th>
          <h6>Địa chỉ</h6>
        </th>
        <th>
          <h6>Số điện thoại</h6>
        </th>
        <th>
          <h6>Website</h6>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($data->getItems())): ?>
        <?php foreach ($data->getItems() as $index => $company): ?>
          <tr onclick="window.location.href='<?= url('admin/companies/' . $company->id) ?>'">
            <td class="data-table__id">#
              <?= htmlspecialchars($company->id ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($company->name ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($company->tax_code ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($company->address ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($company->phone ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($company->website ?? 'N/A') ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="99" class="text-center">Không tìm thấy công ty nào.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?php
  $page = $data;
  include BASE_PATH . '/templates/components/pagination.php'
  ?>
</div>