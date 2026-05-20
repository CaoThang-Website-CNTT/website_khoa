<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

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
        Thêm danh mục mới
      </h2>
      <p>Điền thông tin danh mục mới vào các trường dưới đây</p>
    </div>

    <div class="flex gap-2">
      <div>
        <a href="<?= request()->previous(fallback: 'admin/categories') ?>" data-variant="outline" data-size="lg"
          class="btn">
          <i class="fa-solid fa-chevron-left"></i>
          Quay lại
        </a>
      </div>
      <div>
        <button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="submit" data-variant="primary"
          data-size="lg" class="w-full btn">
          <i class="fa-solid fa-floppy-disk"></i>
          Thêm
        </button>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<form id="category-add-form" action="<?= url('admin/categories') ?>" method="POST">
  <?= csrf_field() ?>
  
  <!-- ── Card 1: Thông tin danh mục ── -->
  <div class="card shadow w-full">
    <div class="card__header">
      <legend class="card__title field__legend">Thông tin danh mục mới</legend>
      <p class="card__description field__description">
        Vui lòng điền đầy đủ thông tin danh mục. Những trường có dấu * là bắt buộc.
      </p>
    </div>
    <hr class="separator" />
    <div class="card__content">
      <div class="field-group">

        <div class="field" data-field-required>
          <label class="field__label" for="name">Tên danh mục</label>
          <input id="name" class="field__input" type="text" name="name" placeholder="VD: Tin tức sự kiện" value="">
        </div>

        <div class="field">
          <label class="field__label" for="slug">Slug</label>
          <input id="slug" class="field__input" type="text" name="slug" placeholder="ten-danh-muc" value="">
        </div>

        <div class="field">
          <label class="field__label" for="parent_id">Danh mục cha</label>
          <select id="parent_id" class="field__input" name="parent_id">
            <option value="">-- Không có (danh mục gốc) --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat->id) ?>">
                <?= htmlspecialchars($cat->name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label class="field__label" for="description">Mô tả</label>
          <textarea id="description" class="field__input" name="description" rows="4"
            placeholder="Mô tả ngắn về danh mục này"></textarea>
        </div>

      </div>
    </div>
  </div>
</form>

<!-- ── Confirm Modal ── -->
<div class="modal detail-modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">
      Những thao tác này sẽ không thể hoàn tác.
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

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#category-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const nameInput = document.querySelector('#name');
    const slugInput = document.querySelector('#slug');

    // ── Slug auto-generation ──────────────────────────────────────────────
    nameInput.addEventListener('input', () => {
      if (slugInput.dataset.manual) return;
      slugInput.value = nameInput.value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
    });

    slugInput.addEventListener('input', () => {
      slugInput.dataset.manual = 'true';
    });

    confirmBtn.addEventListener('click', function () {
      form.submit();
    });
  });
</script>
