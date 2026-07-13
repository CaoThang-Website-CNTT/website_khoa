<?php $layout->start('head') ?>
<link rel="stylesheet" href="<?= url('public/css/company_duplicates.css') ?>">
<?php $layout->end() ?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Công ty có dấu hiệu trùng lặp</h2>
<p class="title-wrapper__description">Danh sách các công ty có khả năng bị trùng lặp trong hệ thống.</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/companies') ?>" data-variant="outline" data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i> Quay lại
</a>
<?php $layout->end() ?>

<?php $layout->start("content") ?>

<div class="flex flex-col gap-4">
  <?php if (empty($groups)): ?>
    <div class="card p-2 text-center">
      Không tìm thấy công ty nào có dấu hiệu trùng lặp.
    </div>
  <?php else: ?>
    <?php foreach ($groups as $group): ?>
      <?php $parent = $group['parent']; ?>
      <div class="card p-0">
        <!-- Phần đầu: công ty gốc -->
        <div class="company-group-header">
          <div class="flex flex-col gap-2">
            <h3 class="font-semibold text-lg">
              #<?= $parent['id'] ?> - <?= htmlspecialchars($parent['name']) ?>
            </h3>
            <div class="flex gap-2 align-center">
              <span>MST: <?= htmlspecialchars($parent['tax_code'] ?: 'Chưa có') ?></span>
              <span class="badge" data-variant="<?= $parent['is_verified'] ? 'primary' : 'warning' ?>" data-size="sm">
                <?= $parent['is_verified'] ? 'Đã xác thực' : 'Chưa xác thực' ?>
              </span>
            </div>
          </div>

          <div class="company-group-header__actions">
            <?php
            // Thu thập tất cả các ID của children để gộp nhanh một lần
            $childIds = array_map(fn($c) => $c['id'], $group['children']);
            $childIdsJson = json_encode($childIds);
            ?>
            <form id="form-bulk-merge-<?= $parent['id'] ?>" action="<?= url('admin/companies/bulk-quick-merge') ?>"
              method="POST" class="m-0 inline-block">
              <?= csrf_field() ?>
              <?php foreach ($childIds as $cid): ?>
                <input type="hidden" name="source_ids[]" value="<?= $cid ?>">
              <?php endforeach; ?>
              <input type="hidden" name="target_id" value="<?= $parent['id'] ?>">
              <button type="button" class="btn btn-confirm-trigger" data-variant="primary" data-size="md"
                data-form-id="form-bulk-merge-<?= $parent['id'] ?>"
                data-message="Bạn có chắc chắn muốn gộp TẤT CẢ các công ty dưới đây vào công ty gốc (#<?= $parent['id'] ?>)? Toàn bộ sinh viên và giấy giới thiệu thực tập sẽ được tự động chuyển sang công ty gốc, sau đó các công ty bị gộp sẽ bị xóa khỏi hệ thống. Lưu ý: Hành động này không thể hoàn tác.">
                <i class="fa-solid fa-layer-group"></i> Gộp tất cả vào Gốc
              </button>
            </form>
          </div>
        </div>

        <!-- Nội dung: công ty có dấu hiệu trùng -->
        <div class="p-2">
          <div class="font-semibold">Danh sách công ty có dấu hiệu trùng lặp (Sẽ bị xóa nếu gộp):</div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr>
                  <th class="company-duplicates-table__th">ID</th>
                  <th class="company-duplicates-table__th">Tên công ty</th>
                  <th class="company-duplicates-table__th">Mã số thuế</th>
                  <th class="company-duplicates-table__th">Lý do</th>
                  <th class="company-duplicates-table__th">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($group['children'] as $child): ?>
                  <tr>
                    <td class="text-sm p-2">#<?= $child['id'] ?></td>
                    <td class="text-sm p-2">
                      <div class="font-medium"><?= htmlspecialchars($child['name']) ?></div>
                      <span class="badge mt-1" data-variant="<?= $child['is_verified'] ? 'primary' : 'warning' ?>"
                        data-size="sm">
                        <?= $child['is_verified'] ? 'Đã xác thực' : 'Chưa xác thực' ?>
                      </span>
                    </td>
                    <td class="text-sm p-2"><?= htmlspecialchars($child['tax_code'] ?: 'Không có') ?></td>
                    <td class="text-sm p-2">
                      <span class="badge" data-variant="<?= $child['reason'] === 'Cùng MST' ? 'destructive' : 'warning' ?>">
                        <?= htmlspecialchars($child['reason']) ?>
                      </span>
                    </td>
                    <td class="text-sm p-2 company-duplicates-table__td--action">
                      <form id="form-quick-merge-<?= $child['id'] ?>" action="<?= url('admin/companies/quick-merge') ?>"
                        method="POST" class="m-0 inline-block">
                        <?= csrf_field() ?>
                        <input type="hidden" name="source_id" value="<?= $child['id'] ?>">
                        <input type="hidden" name="target_id" value="<?= $parent['id'] ?>">
                        <button type="button" class="btn btn-confirm-trigger" data-variant="secondary" data-size="sm"
                          title="Gộp nhanh và xóa công ty này" data-form-id="form-quick-merge-<?= $child['id'] ?>"
                          data-message="Bạn có chắc muốn gộp nhanh công ty #<?= $child['id'] ?> vào công ty gốc #<?= $parent['id'] ?>? Toàn bộ sinh viên và giấy giới thiệu thực tập sẽ được tự động chuyển sang công ty gốc, sau đó công ty #<?= $child['id'] ?> sẽ bị xóa. Lưu ý: Hành động này không thể hoàn tác.">
                          <i class="fa-solid fa-bolt"></i> Gộp nhanh
                        </button>
                      </form>
                      <a href="<?= url("admin/companies/{$child['id']}/merge?q=" . urlencode($parent['name'])) ?>" class="btn"
                        data-variant="outline" data-size="sm" title="Mở trang gộp thủ công">Gộp thủ công
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Hộp thoại xác nhận -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận thao tác gộp</h2>
    <p class="modal__description" id="confirm-modal-message">
      Bạn có chắc chắn muốn thực hiện thao tác này? Toàn bộ sinh viên và giấy giới thiệu thực tập của các công ty bị gộp
      sẽ được tự động chuyển sang công ty gốc, sau đó các công ty bị gộp sẽ bị xóa khỏi hệ thống. Lưu ý: Hành động này
      không thể hoàn tác.
    </p>
  </div>

  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Chắc chắn</button>
  </div>

  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<?php $layout->end() ?>

<?php $layout->start('scripts') ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    let currentFormId = null;
    const modalMessage = document.getElementById('confirm-modal-message');

    document.querySelectorAll('.btn-confirm-trigger').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        currentFormId = btn.getAttribute('data-form-id');
        modalMessage.textContent = btn.getAttribute('data-message');

        if (typeof ModalHandler !== 'undefined' && ModalHandler.instance) {
          ModalHandler.instance.open('#confirm-modal');
        } else {
          console.error('ModalHandler chưa được định nghĩa');
        }
      });
    });

    document.getElementById('confirm-modal-btn').addEventListener('click', () => {
      if (currentFormId) {
        document.getElementById(currentFormId).submit();
      }
    });
  });
</script>
<?php $layout->end() ?>