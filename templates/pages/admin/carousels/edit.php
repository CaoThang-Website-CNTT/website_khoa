<?php
$errors = request()->session()->getErrors()() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

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

<div class="detail-layout">
  <div class="detail-layout__main flex-1">
    <div class="card shadow">
      <div class="card__header">
        <div class="card__title">
          <h6>Các slide trong Carousel</h6>
        </div>
        <div class="card__description">
          Dùng ↑ ↓ để sắp xếp thứ tự hiển thị của các slide.
        </div>
      </div>

      <div class="card__content space-y-4">
        <?php include BASE_PATH . '/templates/components/flash_alert.php'; ?>

        <?php if (!empty($carousel->slides)): ?>
          <?php foreach ($carousel->slides as $index => $slide): ?>
            <?php
            $canUp = $index > 0;
            $canDown = $index < count($carousel->slides) - 1;
            ?>
            <div class="flex items-center gap-2">

              <div class="shrink-0 flex flex-col overflow-hidden rounded-xl border">
                <form method="POST" action="<?= url('admin/carousels/' . $carousel->id . '/slides/reorder') ?>">
                  <?= csrf_field() ?>
                  <?php if ($canUp): ?>
                    <input type="hidden" name="move_id" value="<?= $carousel->id ?>">
                    <input type="hidden" name="direction" value="up">
                  <?php endif; ?>
                  <button type="<?= $canUp ? 'submit' : 'button' ?>" data-variant="outline" data-size="md"
                    class="btn border-0 rounded-none" <?= !$canUp ? 'disabled' : '' ?> title="Lên">↑</button>
                </form>

                <form method="POST" action="<?= url('admin/carousels/' . $carousel->id . '/slides/reorder') ?>">
                  <?= csrf_field() ?>
                  <?php if ($canDown): ?>
                    <input type="hidden" name="move_id" value="<?= $carousel->id ?>">
                    <input type="hidden" name="direction" value="down">
                  <?php endif; ?>
                  <button type="<?= $canDown ? 'submit' : 'button' ?>" data-variant="outline" data-size="md"
                    class="btn border-0 rounded-none" <?= !$canDown ? 'disabled' : '' ?> title="Xuống">↓</button>
                </form>
              </div>

              <div class="flex-1 flex items-center gap-3 border rounded-md px-3 py-3 h-full">
                <?php if (!empty($slide->image_path)): ?>
                  <img src="<?= url($slide->image_path) ?>" alt="" class="rounded object-cover border"
                    style="width: 80px; height: 40px;">
                <?php else: ?>
                  <div class="bg-gray-100 border flex items-center justify-center text-xs text-gray-400"
                    style="width: 80px; height: 40px;">N/A</div>
                <?php endif; ?>

                <span class="flex-1 font-medium truncate">
                  <?= htmlspecialchars($slide->title ?? '') ?>
                  <?= htmlspecialchars($slide->title_highlight ?? '') ?>
                </span>

                <div>
                  <?php if ($slide->isActive()): ?>
                    <span class="badge" data-variant="primary">Hiển thị</span>
                  <?php else: ?>
                    <span class="badge" data-variant="secondary">Đã ẩn</span>
                  <?php endif; ?>

                  <a href="<?= url('admin/carousels/' . $carousel->id . '/slides/' . $slide->id) ?>" data-variant="outline"
                    data-size="md" class="btn ml-2">
                    Xem
                  </a>
                </div>
              </div>

            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-gray-500">
            Chưa có slide nào. Thêm slide đầu tiên bên dưới.
          </p>
        <?php endif; ?>
      </div>

      <div class="card__footer">
        <a href="<?= url('admin/carousels/' . $carousel->id . '/slides/create') ?>" data-variant="primary"
          data-size="lg" class="btn w-full">
          Thêm slide mới
        </a>
      </div>
    </div>
  </div>

  <div class="detail-layout__sidebar">
    <div class="card shadow">
      <div class="card__header flex justify-between items-center">
        <div>
          <div class="card__title">
            <h6>Thông tin Carousel</h6>
          </div>
        </div>
      </div>

      <div class="card__content">
        <form id="carousel-edit-form" method="POST" action="<?= url('admin/carousels/' . $carousel->id) ?>">
          <?= csrf_field() ?>
          <div class="field-group">
            <div class="field" data-field-required>
              <label for="name">Tên Carousel</label>
              <input id="name" class="field__input" type="text" name="name"
                value="<?= htmlspecialchars($old_input['name'] ?? $carousel->name ?? '') ?>">
            </div>

            <div class="field">
              <label for="slug">Đường dẫn (Slug)</label>
              <input id="slug" class="field__input" type="text" name="slug"
                value="<?= htmlspecialchars($old_input['slug'] ?? $carousel->slug ?? '') ?>">
            </div>

            <div class="field">
              <label class="field__toggle">
                <?php $isActive = isset($old_input['is_active']) ? !empty($old_input['is_active']) : $carousel->isActive(); ?>
                <input type="checkbox" name="is_active" id="is_active" value="1" <?= $isActive ? 'checked' : '' ?>>
                <span class="field__toggle-track"></span>
                <span class="field__toggle-label">Hiển thị Carousel</span>
              </label>
            </div>
          </div>
        </form>
      </div>

      <div class="card__footer">
        <button data-modal-trigger="#delete-modal" type="button" data-variant="destructive" data-size="sm" class="btn">
          Xóa
        </button>
        <button data-modal-trigger="#confirm-modal" id="update-submit-btn" type="button" data-variant="primary"
          data-size="lg" class="btn w-full">
          Lưu thông tin
        </button>
      </div>
    </div>
  </div>

</div>

<form id="carousel-delete-form" method="POST" action="<?= url('admin/carousels/delete/' . $carousel->id) ?>">
  <?= csrf_field() ?></form>

<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận chỉnh sửa</h2>
    <p class="modal__description">Bạn có chắc muốn lưu các thay đổi này?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Lưu</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
      <path
        d="M55.1 73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L147.2 256 9.9 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192.5 301.3 329.9 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.8 256 375.1 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192.5 210.7 55.1 73.4z" />
    </svg>
  </button>
</div>

<div class="modal" id="delete-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title text-red-600">Xóa Carousel</h2>
    <p class="modal__description">Bạn có chắc chắn muốn xóa Carousel này? Các Slides cũng sẽ bị xóa.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="delete-confirm-btn" data-variant="destructive" data-size="lg" class="btn" type="button">Xóa</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
      <path
        d="M55.1 73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L147.2 256 9.9 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192.5 301.3 329.9 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.8 256 375.1 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192.5 210.7 55.1 73.4z" />
    </svg>
  </button>
</div>

<div class="modal-overlay" data-modal-close></div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const editForm = document.querySelector('#carousel-edit-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    if (confirmBtn) confirmBtn.addEventListener('click', () => editForm.submit());

    const deleteForm = document.querySelector('#carousel-delete-form');
    const deleteConfirmBtn = document.querySelector('#delete-confirm-btn');
    if (deleteConfirmBtn) deleteConfirmBtn.addEventListener('click', () => deleteForm.submit());
  });
</script>