<?php
$errors = request()->session()->getErrors()() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<?php if ($flash = request()->session()->getFlash()): ?>
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
      <h2 class="title text-2xl font-semibold">Thêm carousel mới</h2>
      <p>Điền thông tin carousel và các slide bên dưới</p>
    </div>
    <div class="flex gap-2">
      <div>
        <a href="<?= request()->previous(fallback: 'admin/carousels') ?>" data-variant="outline" data-size="lg"
          class="btn">
          <i class="fa-solid fa-chevron-left"></i>
          Quay lại
        </a>
      </div>
      <div>
        <button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="button" data-variant="primary"
          data-size="lg" class="w-full btn">
          <i class="fa-solid fa-floppy-disk"></i>
          Thêm
        </button>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<form class="detail-layout" id="carousel-add-form" action="<?= url('admin/carousels') ?>" method="POST">
  <?= csrf_field() ?>
  <div class="detail-layout__main">
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin carousel</legend>
          <p class="field__description">Những trường có dấu * là bắt buộc.</p>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">

            <div class="field" data-field-required>
              <label class="field__label" for="name">Tên carousel</label>
              <input id="name" class="field__input" type="text" name="name" placeholder="VD: Slider Trang chủ" value="">
            </div>

            <div class="field" data-field-readonly>
              <label class="field__label" for="slug">Slug</label>
              <input id="slug" class="field__input" type="text" name="slug" placeholder="slider-trang-chu" value=""
                readonly>
              <p class="field__description">Slug được tự động tạo từ tên carousel.</p>
            </div>

          </div>
        </div>
      </fieldset>
    </div>
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="card__title field__legend">Danh sách slides</legend>
          <p class="card_description field__description">Thêm và sắp xếp các slide cho carousel này.</p>
          <button type="button" id="add-slide-btn" data-variant="outline" data-size="lg" class="btn card__action">
            <i class="fa-solid fa-plus"></i>
            Thêm slide
          </button>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div id="slides-container" class="space-y-4">
            <!-- Slides will be injected here by JS -->
            <div id="slides-empty-hint" class="empty">
              <div class="empty__header">
                <div class="empty__title">
                  Chưa có slide nào
                </div>
                <div class="empty__description">
                  Nhấn "Thêm slide" để tạo slide mới.
                </div>
              </div>
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
          <legend class="field__legend">Trạng thái</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">

            <div class="field" data-orientation="horizontal">
              <label class="field__label" for="is_active">Kích hoạt carousel</label>
              <input id="is_active" class="field__input" type="checkbox" name="is_active" value="1" checked>
            </div>
            <p class="field__description">Carousel sẽ hiển thị trên trang web khi được kích hoạt.</p>

          </div>
        </div>
      </fieldset>
    </div>
  </div>
</form>

<!-- Slide template -->
<template id="slide-template">
  <div class="slide-item card shadow" data-dnd-draggable data-slide-index="">
    <div class="card__header">
      <legend class="card__title field__legend">
        <i class="fa-solid fa-grip-vertical"></i>
        Slide <span class="slide-number"></span>
      </legend>
      <button type="button" class="btn card__action remove-slide-btn" data-variant="destructive" data-size="sm">
        <i class="fa-solid fa-trash"></i>
        Xóa
      </button>
    </div>
    <hr class="separator" />
    <div class="card__content">
      <div class="field-group">

        <!-- sort_order -->
        <input type="hidden" class="slide-sort-order" name="slides[__INDEX__][sort_order]" value="">

        <!-- is_active -->
        <div class="field" data-orientation="horizontal">
          <label class="field__label">Kích hoạt slide</label>
          <input type="checkbox" class="field__input" name="slides[__INDEX__][is_active]" value="1" checked>
        </div>

        <!-- title + title_highlight -->
        <div class="grid grid-cols-2 gap-4">
          <div class="field" data-field-required>
            <label class="field__label">Tiêu đề</label>
            <input class="field__input" type="text" name="slides[__INDEX__][title]"
              placeholder="Tiêu đề chính của slide">
          </div>
          <div class="field">
            <label class="field__label">Tiêu đề nổi bật</label>
            <input class="field__input" type="text" name="slides[__INDEX__][title_highlight]"
              placeholder="Phần in đậm / màu khác">
          </div>
        </div>

        <!-- description -->
        <div class="field">
          <label class="field__label">Mô tả</label>
          <textarea class="field__input" name="slides[__INDEX__][description]"
            placeholder="Mô tả ngắn hiển thị trên slide" rows="2"></textarea>
        </div>

        <!-- image_path + image_alt -->
        <div class="grid grid-cols-2 gap-4">
          <div class="field" data-field-required>
            <label class="field__label">Đường dẫn ảnh</label>
            <input class="field__input" type="text" name="slides[__INDEX__][image_path]"
              placeholder="/uploads/slides/anh.jpg">
          </div>
          <div class="field">
            <label class="field__label">Alt text (ảnh)</label>
            <input class="field__input" type="text" name="slides[__INDEX__][image_alt]"
              placeholder="Mô tả ảnh cho SEO / accessibility">
          </div>
        </div>

        <!-- CTA -->
        <div class="grid grid-cols-2 gap-4">
          <div class="field">
            <label class="field__label">Nhãn nút CTA</label>
            <input class="field__input" type="text" name="slides[__INDEX__][cta_label]" placeholder="VD: Tìm hiểu thêm">
          </div>
          <div class="field">
            <label class="field__label">Kiểu nút CTA</label>
            <select class="field__input" name="slides[__INDEX__][cta_variant]">
              <option value="primary">Primary</option>
              <option value="secondary">Secondary</option>
              <option value="outline">Outline</option>
            </select>
          </div>
        </div>
        <div class="field">
          <label class="field__label">URL nút CTA</label>
          <input class="field__input" type="text" name="slides[__INDEX__][cta_url]"
            placeholder="https://... hoặc /duong-dan">
        </div>

        <!-- use_custom_html toggle -->
        <div class="field" data-orientation="horizontal">
          <label class="field__label">Dùng HTML tuỳ chỉnh</label>
          <input type="checkbox" class="field__input use-custom-html-toggle" name="slides[__INDEX__][use_custom_html]"
            value="1">
        </div>

        <!-- custom_html (ẩn mặc định) -->
        <div class="field custom-html-field" style="display: none;">
          <label class="field__label">Custom HTML</label>
          <textarea class="field__input field__input--mono" name="slides[__INDEX__][custom_html]"
            placeholder="<div>Nội dung HTML tuỳ chỉnh...</div>" rows="5" spellcheck="false"></textarea>
          <p class="field__description">Khi bật, nội dung HTML này sẽ thay thế title/description mặc định.</p>
        </div>

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
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const manager = new DragDropManager();

    const form = document.querySelector('#carousel-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const nameInput = document.querySelector('#name');
    const slugInput = document.querySelector('#slug');
    const addSlideBtn = document.querySelector('#add-slide-btn');
    const slidesContainer = document.querySelector('#slides-container');
    const emptyHint = document.querySelector('#slides-empty-hint');
    const template = document.querySelector('#slide-template');

    let slideCount = 0;

    nameInput.addEventListener('input', function () {
      slugInput.value = Utils.toCleanAscii(this.value)
        .toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    });

    function reindexSlides() {
      const items = slidesContainer.querySelectorAll('.slide-item');
      emptyHint.style.display = items.length === 0 ? 'flex' : 'none';
      items.forEach((item, i) => {
        item.querySelector('.slide-number').textContent = i + 1;
        item.querySelector('.slide-sort-order').value = i + 1;
      });
      manager.reindexGroup('slides');
    }

    addSlideBtn.addEventListener('click', () => {
      const index = slideCount++;
      const id = 'slide-' + index;

      const clone = template.content.cloneNode(true);
      const el = clone.querySelector('.slide-item');

      // Fill __INDEX__ placeholders
      el.querySelectorAll('[name]').forEach(input => {
        input.name = input.name.replace(/__INDEX__/g, index);
      });

      el.dataset.slideIndex = id;

      const toggle = el.querySelector('.use-custom-html-toggle');
      const htmlField = el.querySelector('.custom-html-field');
      toggle.addEventListener('change', () => {
        htmlField.style.display = toggle.checked ? 'block' : 'none';
      });

      el.querySelector('.remove-slide-btn').addEventListener('click', () => {
        const s = manager.registry.draggables.get(id);
        s?.destroy?.();

        el.remove();
        reindexSlides();
      });

      slidesContainer.appendChild(el);

      new Sortable({
        id,
        element: el,
        handle: el.querySelector('.card__header'),
        group: 'slides',
        index: slideCount - 1,
      }, manager)

      reindexSlides();
    });

    manager.monitor.addEventListener('dragend', e => {
      const s = e.operation.source;
      if (!e.canceled && isSortable(s)) {
        s.initialIndex = s.index;
        s.initialGroup = s.group;
        reindexSlides();
      }
    });

    confirmBtn.addEventListener('click', () => form.submit());
  });
</script>