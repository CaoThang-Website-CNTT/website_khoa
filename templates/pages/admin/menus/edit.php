<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];

$isEditable = $menu->isEditable();
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>
<?php
function renderMenuItems(array $items, object $menu, int $depth = 0): void
{
  foreach ($items as $item):
    $canUp = in_array($item->order_state, ['can_reorder', 'no_down']);
    $canDown = in_array($item->order_state, ['can_reorder', 'no_up']);
    ?>
    <div class="flex items-center gap-2 ml-<?= $depth * 4 ?>">

      <div class="shrink-0 flex flex-col overflow-hidden rounded-xl border">

        <form method="POST" action="<?= url('admin/menus/' . $menu->id . '/items/reorder') ?>">
          <?= csrf_field() ?>
          <?php if ($canUp): ?>
            <input type="hidden" name="move_id" value="<?= $item->id ?>">
            <input type="hidden" name="direction" value="up">
          <?php endif; ?>
          <button type="<?= $canUp ? 'submit' : 'button' ?>" data-variant="outline" data-size="md"
            class="btn border-0 rounded-none" <?= !$canUp ? 'disabled' : '' ?> title="Lên">↑</button>
        </form>

        <form method="POST" action="<?= url('admin/menus/' . $menu->id . '/items/reorder') ?>">
          <?= csrf_field() ?>
          <?php if ($canDown): ?>
            <input type="hidden" name="move_id" value="<?= $item->id ?>">
            <input type="hidden" name="direction" value="down">
          <?php endif; ?>
          <button type="<?= $canDown ? 'submit' : 'button' ?>" data-variant="outline" data-size="md"
            class="btn border-0 rounded-none" <?= !$canDown ? 'disabled' : '' ?> title="Xuống">↓</button>
        </form>
      </div>

      <!-- Item row -->
      <div class="flex-1 flex items-center gap-2 border rounded-md px-3 py-3 h-full">
        <span class="flex-1">
          <?= htmlspecialchars($item->label) ?>
        </span>
        <span class="font-mono text-sm">
          <?= htmlspecialchars($item->url) ?>
        </span>
        <div>
          <a href="<?= url('admin/menus/' . $menu->id . '/items/' . $item->id) ?>" data-variant="outline" data-size="md"
            class="btn">
            Xem
          </a>
        </div>
      </div>

    </div>
    <?php if ($item->hasChildren()): ?>
      <?php renderMenuItems($item->children, $menu, $depth + 1); ?>
    <?php endif; ?>

  <?php endforeach;
}
?>

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
  <!-- LEFT - MENU ITEM TREE -->
  <div class="detail-layout__main flex-1">
    <div class="card shadow">
      <div class="card__header">
        <div class="card__title">
          <h6>Các mục trong menu</h6>
        </div>
        <div class="card__description">
          Dùng ↑ ↓ để sắp xếp thứ tự. Xóa một mục sẽ xóa toàn bộ mục con bên dưới.
        </div>
      </div>

      <div class="card__content space-y-4">
        <?php include BASE_PATH . '/templates/components/flash_alert.php'; ?>

        <?php if (!empty($items)): ?>
          <?php renderMenuItems($items, $menu); ?>
        <?php else: ?>
          <p>
            Chưa có mục nào. Thêm mục đầu tiên bên dưới.
          </p>
        <?php endif; ?>
      </div>

      <div class="card__footer">
        <a href="<?= url('admin/menus/' . $menu->id . '/items/create') ?>" data-variant="primary" data-size="lg"
          class="btn w-full">
          Thêm mục mới
        </a>
      </div>
    </div>
  </div>

  <!-- RIGHT - MENU INFO -->
  <div class="detail-layout__sidebar">
    <div class="card shadow">
      <div class="card__header">
        <div class="card__title">
          <h6>Thông tin nhóm menu</h6>
        </div>
      </div>
      <div class="card__content">
        <form id="menu-edit-form" method="POST" action="<?= url('admin/menus/' . $menu->id) ?>">
          <?= csrf_field() ?>
          <div class="field-group">
            <div class="field">
              <label for="label">Tên hiển thị</label>
              <input id="label" class="field__input" type=" text" name="label"
                value="<?= htmlspecialchars($menu->label ?? '') ?>" placeholder="VD: Liên kết nhanh" required>
            </div>
            <div class="field">
              <label>Key</label>
              <input class="field__input" type="text" name="key" value="<?= htmlspecialchars($menu->key) ?>"
                <?= $isEditable ? '' : 'disabled' ?>>
            </div>
            <div class="field">
              <label>Loại</label>
              <input class="field__input" type="text" value="<?= $menu->isConst() ? 'Hệ thống' : 'Tuỳ chỉnh' ?>"
                disabled>
            </div>
            <div class="field">
              <label>Mô tả</label>
              <input class="field__input" type="text" name="description"
                value="<?= htmlspecialchars($menu->description ?? '—') ?>" <?= $isEditable ? '' : 'disabled' ?>>
            </div>
          </div>
        </form>
      </div>
      <?php if ($isEditable): ?>
        <div class="card__footer">
          <button data-modal-trigger="#confirm-modal" id="update-submit-btn" type="button" data-variant="primary"
            data-size="lg" class="btn">
            Sửa thông tin menu
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Confirm Update Modal -->
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

<div class="modal-overlay" data-modal-close></div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const editForm = document.querySelector('#menu-edit-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');

    console.log(document.querySelector('#menu-edit-form'));

    confirmBtn.addEventListener('click', () => editForm.submit());
  });
</script>