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
      <h2 class="title text-2xl font-semibold">Thêm nhóm menu mới</h2>
      <p>Điền thông tin nhóm menu và các mục bên dưới</p>
    </div>
    <div class="flex gap-2">
      <a href="<?= request()->previous(fallback: 'admin/menus') ?>" data-variant="outline" data-size="lg" class="btn">
        <i class="fa-solid fa-chevron-left"></i>
        Quay lại
      </a>
      <button data-modal-trigger="#confirm-modal" type="button" data-variant="primary" data-size="lg" class="btn">
        <i class="fa-solid fa-floppy-disk"></i>
        Thêm
      </button>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<form class="detail-layout" id="menu-add-form" action="<?= url('admin/menus') ?>" method="POST">
  <div class="detail-layout__main">

    <!-- ── Card 1: Thông tin nhóm menu ── -->
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin nhóm menu</legend>
          <p class="field__description">Những trường có dấu * là bắt buộc.</p>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required data-field-max="60">
                <label class="field__label" for="key">Key (định danh)</label>
                <input id="key" class="field__input" type="text" name="key" placeholder="VD: header_menu" value="">
                <p class="field__description">Chỉ dùng chữ thường, số và dấu gạch dưới. Duy nhất trong hệ thống.</p>
              </div>

              <div class="field" data-field-required data-field-max="100">
                <label class="field__label" for="label">Tên hiển thị</label>
                <input id="label" class="field__input" type="text" name="label" placeholder="VD: Menu Chính" value="">
              </div>
            </div>

            <div class="field" data-field-max="255">
              <label class="field__label" for="description">Mô tả</label>
              <textarea id="description" class="field__input" name="description"
                placeholder="Mô tả ngắn về nhóm menu này"></textarea>
            </div>

            <div class="field">
              <label class="field__label" for="sort_order">Thứ tự hiển thị</label>
              <input id="sort_order" class="field__input" type="number" name="sort_order" placeholder="0" min="0"
                value="0">
              <p class="field__description">Số nhỏ hơn sẽ hiển thị trước.</p>
            </div>

          </div>
        </div>
      </fieldset>
    </div>

    <!-- ── Card 2: Menu Items ── -->
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <div class="flex justify-between items-center">
            <div>
              <legend class="field__legend">Các mục menu</legend>
              <p class="field__description">Thêm các mục cho nhóm menu này. Có thể thiết lập quan hệ cha–con.</p>
            </div>
            <button type="button" id="add-item-btn" data-variant="outline" data-size="lg" class="btn">
              <i class="fa-solid fa-plus"></i>
              Thêm mục
            </button>
          </div>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div id="items-container">
            <p id="items-empty-hint" class="field__description" style="text-align:center; padding: 1.5rem 0;">
              Chưa có mục nào. Nhấn "Thêm mục" để bắt đầu.
            </p>
          </div>
        </div>
      </fieldset>
    </div>

  </div>
</form>

<!-- ── Item template (cloned by JS) ── -->
<template id="item-template">
  <div class="item-row card shadow" data-item-index="" style="margin-bottom: 1rem;">
    <div class="card__header">
      <div class="flex justify-between items-center">
        <legend class="field__legend">
          Mục <span class="item-number"></span>
        </legend>
        <button type="button" class="btn remove-item-btn" data-variant="destructive" data-size="sm">
          <i class="fa-solid fa-trash"></i>
          Xóa
        </button>
      </div>
    </div>
    <hr class="separator" />
    <div class="card__content">
      <div class="field-group">

        <div class="grid grid-cols-2 gap-4">
          <div class="field" data-field-required>
            <label class="field__label">Nhãn</label>
            <input class="field__input item-label" type="text" name="items[__INDEX__][label]"
              placeholder="VD: Trang chủ">
          </div>
          <div class="field" data-field-required>
            <label class="field__label">URL</label>
            <input class="field__input" type="text" name="items[__INDEX__][url]"
              placeholder="/duong-dan hoặc https://...">
          </div>
        </div>

        <div class="field">
          <label class="field__label">Mục cha</label>
          <select class="field__input parent-ref-select" name="items[__INDEX__][parent_ref]">
            <option value="">-- Không có (mục gốc) --</option>
            <!-- Options injected by JS whenever an item is added/removed/renamed -->
          </select>
          <p class="field__description">Chọn mục cha để tạo menu con. Chỉ có thể chọn các mục khác trong danh sách.</p>
        </div>

        <!-- sort_order = index, managed by JS -->
        <input type="hidden" name="items[__INDEX__][sort_order]" class="item-sort-order" value="">

      </div>
    </div>
  </div>
</template>

<!-- ── Confirm Modal ── -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">Những thao tác này sẽ không thể hoàn tác.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Chắc chắn</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
      <path
        d="M55.1 73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L147.2 256 9.9 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192.5 301.3 329.9 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.8 256 375.1 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192.5 210.7 55.1 73.4z" />
    </svg>
  </button>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#menu-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const addItemBtn = document.querySelector('#add-item-btn');
    const container = document.querySelector('#items-container');
    const emptyHint = document.querySelector('#items-empty-hint');
    const template = document.querySelector('#item-template');

    // ── Key auto-format ───────────────────────────────────────────────────
    document.querySelector('#key').addEventListener('input', function () {
      // Force snake_case: lowercase, spaces → underscore, strip invalid chars
      this.value = this.value
        .toLowerCase()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_]/g, '');
    });

    // ── Item registry: { index, el, labelInput } ──────────────────────────
    let itemCount = 0;
    const registry = []; // maintains insertion order, nulled on remove

    // Rebuild all parent-ref dropdowns after any mutation
    function syncParentSelects() {
      // Build option list from current live items
      const options = registry
        .filter(Boolean)
        .map(entry => ({ value: entry.index, label: entry.labelInput.value.trim() || `Mục ${entry.index + 1}` }));

      registry.filter(Boolean).forEach(entry => {
        const sel = entry.el.querySelector('.parent-ref-select');
        const current = sel.value;

        // Rebuild options, excluding self
        sel.innerHTML = '<option value="">-- Không có (mục gốc) --</option>';
        options
          .filter(o => o.value !== entry.index)
          .forEach(o => {
            const opt = document.createElement('option');
            opt.value = o.value;
            opt.textContent = o.label;
            if (String(o.value) === current) opt.selected = true;
            sel.appendChild(opt);
          });
      });
    }

    // Reindex sort_order + item numbers after any mutation
    function reindex() {
      const live = registry.filter(Boolean);
      emptyHint.style.display = live.length === 0 ? 'block' : 'none';
      live.forEach((entry, pos) => {
        entry.el.querySelector('.item-number').textContent = pos + 1;
        entry.el.querySelector('.item-sort-order').value = pos;
      });
      syncParentSelects();
    }

    // ── Add item ──────────────────────────────────────────────────────────
    addItemBtn.addEventListener('click', () => {
      const index = itemCount++;
      const clone = template.content.cloneNode(true);
      const el = clone.querySelector('.item-row');

      // Stamp index into all name attributes
      el.querySelectorAll('[name]').forEach(input => {
        input.name = input.name.replace(/__INDEX__/g, index);
      });
      el.dataset.itemIndex = index;

      const labelInput = el.querySelector('.item-label');

      // Live-update parent dropdowns when label changes
      labelInput.addEventListener('input', syncParentSelects);

      // Remove
      el.querySelector('.remove-item-btn').addEventListener('click', () => {
        const pos = registry.findIndex(e => e?.index === index);
        if (pos !== -1) registry[pos] = null;
        el.remove();
        reindex();
      });

      registry.push({ index, el, labelInput });
      container.appendChild(el);
      reindex();
    });

    // ── Submit ────────────────────────────────────────────────────────────
    confirmBtn.addEventListener('click', () => form.submit());
  });
</script>