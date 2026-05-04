<link rel="stylesheet" href="<?= url('public/css/internship_batches.css') ?>">

<!-- Toast khi redirect về đây có set flash -->
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
    <div class="col-6">
      <h2 class="title text-2xl font-semibold">
        Đợt Thực Tập
        <span class="badge" data-variant="primary">
          <?= $data->getTotal() ?>
        </span>
      </h2>
      <p class="text-sm text-muted-foreground">Quản lý các đợt thực tập của khoa</p>
    </div>

    <div class="flex gap-2">
      <a href="<?= url('admin/internship_batches/create') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-plus"></i>
        Thêm đợt mới
      </a>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<div class="table-wrapper shadow rounded-md mt-4">
  <table class="data-table">
    <thead>
      <tr>
        <th></th>
        <th>
          <h6>Tên đợt</h6>
        </th>
        <th>
          <h6>Mô tả</h6>
        </th>
        <th>
          <h6>Khóa</h6>
        </th>
        <th>
          <h6>Bậc học</h6>
        </th>
        <th>
          <h6>Bắt đầu</h6>
        </th>
        <th>
          <h6>Kết thúc</h6>
        </th>
        <th>
          <h6>Công bố</h6>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($data->getItems())): ?>
        <?php foreach ($data->getItems() as $batch): ?>
          <?php
          $batchObj = (object)$batch;
          $statusVariant = 'secondary';
          if ($batchObj->status === 'published') $statusVariant = 'primary';
          if ($batchObj->status === 'closed') $statusVariant = 'destructive';

          $startDate = $batchObj->start_at ? date('d/m/Y', strtotime($batchObj->start_at)) : 'N/A';
          $endDate = $batchObj->end_at ? date('d/m/Y', strtotime($batchObj->end_at)) : 'N/A';
          $publishDate = $batchObj->published_at ? date('d/m/Y', strtotime($batchObj->published_at)) : '-';
          ?>
          <tr class="internship-batches__row" onclick="window.location.href='<?= url('admin/internship_batches/' . $batchObj->id) ?>'">
            <td class="data-table__id">
              <?= '#' . htmlspecialchars($batchObj->id) ?>
            </td>
            <td class="font-medium text-foreground">
              <?= htmlspecialchars($batchObj->title) ?>
            </td>
            <td>
              <div class="internship-batches__description-wrapper">
                <div class="internship-batches__description" title="<?= htmlspecialchars($batchObj->description ?? '') ?>">
                  <?= htmlspecialchars($batchObj->description ?? 'Không có mô tả') ?>
                </div>
              </div>
            </td>
            <td class="text-center">
              <?= htmlspecialchars($batchObj->class_of ?? 'N/A') ?>
            </td>
            <td>
              <?= htmlspecialchars($batchObj->level ?? 'N/A') ?>
            </td>
            <td>
              <?= $startDate ?>
            </td>
            <td>
              <?= $endDate ?>
            </td>
            <td>
              <?= $publishDate ?>
            </td>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="text-center py-8">
              <div class="flex flex-col items-center gap-2">
                <i class="fa-solid fa-folder-open text-4xl text-muted"></i>
                <p>Không tìm thấy đợt thực tập nào.</p>
              </div>
            </td>
          </tr>
        <?php endif; ?>
    </tbody>
  </table>

  <?php
  $page = $data;
  include BASE_PATH . '/templates/components/pagination.php';
  ?>
</div>