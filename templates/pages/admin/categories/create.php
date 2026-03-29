<?php
$errors = request()->getErrors() ?? [];
$old_input = request()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

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

<form class="detail-layout" id="category-add-form" action="<?= url('admin/categories') ?>" method="POST">
  <div class="detail-layout__main">

    <!-- ── Card 1: Thông tin danh mục ── -->
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin danh mục</legend>
          <p class="field__description">
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

            <div class="field" data-field-readonly>
              <label class="field__label" for="slug">Slug</label>
              <input id="slug" class="field__input" type="text" name="slug" placeholder="ten-danh-muc" value="">
              <p class="field__description">Slug được tự động tạo từ tên danh mục</p>
            </div>

            <div class="field">
              <label class="field__label" for="description">Mô tả</label>
              <textarea id="description" class="field__input" name="description"
                placeholder="Mô tả ngắn về danh mục này"></textarea>
            </div>

          </div>
        </div>
      </fieldset>
    </div>

    <!-- ── Card 2: Meta (JSON) ── -->
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Dữ liệu mở rộng (Meta)</legend>
          <p class="field__description">
            Nhập dữ liệu JSON tuỳ chỉnh cho danh mục. Để trống nếu không cần.
          </p>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">

            <div class="field" id="meta-field">
              <label class="field__label" for="meta">Meta JSON</label>
              <textarea id="meta" class="field__input field__input--mono" name="meta" placeholder='{"key": "value"}'
                rows="6" spellcheck="false"></textarea>
              <p class="field__description" id="meta-hint">Dữ liệu phải là JSON hợp lệ.</p>
            </div>

          </div>
        </div>
      </fieldset>
    </div>

  </div>

  <!-- ── Sidebar ── -->
  <div class="detail-layout__sidebar">
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Phân loại</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">

            <div class="field">
              <label class="field__label" for="parent_id">Danh mục cha</label>
              <select id="parent_id" class="field__input" name="parent_id">
                <option value="">-- Không có (danh mục gốc) --</option>
                <?php foreach ($categories as $category): ?>
                  <?php if (!$category->parent_id): // chỉ hiện root để tránh nest sâu ?>
                    <option value="<?= htmlspecialchars($category->id) ?>">
                      <?= htmlspecialchars($category->name) ?>
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </select>
              <p class="field__description">Để trống nếu đây là danh mục gốc.</p>
            </div>

          </div>
        </div>
      </fieldset>
    </div>
  </div>
</form>

<!-- ── Confirm Modal ── -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
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
    const metaInput = document.querySelector('#meta');
    const metaHint = document.querySelector('#meta-hint');

    // ── Slug auto-generation ──────────────────────────────────────────────
    nameInput.addEventListener('input', function () {
      slugInput.value = Utils.toCleanAscii(this.value)
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    });

    confirmBtn.addEventListener('click', function () {
      form.submit();
    });
  });
</script>