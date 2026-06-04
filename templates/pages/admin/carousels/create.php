<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<!-- Modal select media -->
<?php require_once(BASE_PATH . '/templates/components/media_selector_modal.php'); ?>

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">Thêm carousel mới</h2>
      <p>Điền thông tin carousel và các slide bên dưới</p>
    </div>
    <div class="flex gap-2">
      <div>
        <a href="<?= url("admin/carousels") ?>" data-variant="outline" data-size="lg" class="btn">
          <i class="fa-solid fa-chevron-left"></i>
          Quay lại
        </a>
      </div>
      <div>
        <button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="button" data-variant="primary"
          data-size="lg" class="btn">
          <i class="fa-solid fa-floppy-disk"></i>
          Thêm
        </button>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<form id="carousel-add-form" action="<?= url('admin/carousels') ?>" method="POST">
  <?= csrf_field() ?>

  <!-- Hidden inputs for slides will be dynamically injected here -->
  <div id="slides-inputs-container"></div>

  <div class="detail-layout">
    <div class="detail-layout__main flex-1">
      <!-- Carousel Slides Card -->
      <div class="card shadow">
        <div class="card__header">
          <legend class="card__title field__legend">Các slide trong Carousel</legend>
          <p class="card__description field__description">Kéo thả để sắp xếp thứ tự hiển thị của các slide.</p>
          <button class="btn card__action" type="button" id="add-item-btn" data-variant="outline" data-size="lg">
            <i class="fa-solid fa-plus"></i>
            Thêm slide
          </button>
        </div>
        <hr class="separator">
        <div class="card__content">
          <div id="slides-container" class="space-y-2">
            <!-- Slides list empty state by default -->
            <div class="empty" id="slides-empty-hint">
              <div class="empty__header">
                <div class="empty__media">
                  <i class="fa-solid fa-image"></i>
                </div>
                <div class="empty__title">Carousel trống</div>
                <div class="empty__description">
                  Chưa có slide nào. Thêm slide đầu tiên bên dưới.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="detail-layout__sidebar">
      <div class="card shadow">
        <div class="card__header">
          <legend class="card__title field__legend">Thông tin Carousel</legend>
        </div>
        <hr class="separator">
        <div class="card__content">
          <div class="field-group">
            <div class="field" data-field-required>
              <label class="field__label" for="name">Tên Carousel</label>
              <input id="name" class="field__input" type="text" name="name" placeholder="VD: Slider Trang chủ"
                value="<?= htmlspecialchars($old_input['name'] ?? '') ?>">
            </div>

            <div class="field">
              <label class="field__label" for="slug">Slug</label>
              <input id="slug" class="field__input" type="text" name="slug" placeholder="slider-trang-chu"
                value="<?= htmlspecialchars($old_input['slug'] ?? '') ?>">
              <p class="field__description">Slug được tự động tạo từ tên carousel.</p>
            </div>

            <div class="field" data-orientation="horizontal">
              <label class="field__label" for="is_active">Kích hoạt</label>
              <input id="is_active" class="field__input" type="checkbox" name="is_active" value="1"
                <?= !isset($old_input['is_active']) || $old_input['is_active'] ? 'checked' : '' ?>>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- ── Confirm Carousel Create Modal ── -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận tạo Carousel</h2>
    <p class="modal__description">Bạn có chắc chắn muốn lưu Carousel này cùng toàn bộ slide hiện tại?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Xác nhận</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<!-- ── Slide Edit / Create Modal ── -->
<div class="modal detail-modal" id="slide-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title" id="slide-modal-title">Thêm slide mới</h2>
    <p class="modal__description">Vui lòng nhập thông tin chi tiết cho slide bên dưới.</p>
  </div>

  <form class="detail-modal__form space-y-4" id="slide-form" onsubmit="event.preventDefault();">
    <div class="field-group">
      <!-- is_active -->
      <div class="field" data-orientation="horizontal">
        <label class="field__label" for="slide-is-active">Kích hoạt slide</label>
        <input id="slide-is-active" class="field__input" type="checkbox" name="is_active" value="1" checked>
      </div>

      <!-- title + title_highlight -->
      <div class="grid grid-cols-2 gap-4">
        <div class="field" data-field-required>
          <label class="field__label" for="slide-title">Tiêu đề</label>
          <input id="slide-title" class="field__input" type="text" name="title" placeholder="Tiêu đề chính của slide"
            required>
        </div>
        <div class="field">
          <label class="field__label" for="slide-title-highlight">Tiêu đề nổi bật</label>
          <input id="slide-title-highlight" class="field__input" type="text" name="title_highlight"
            placeholder="Phần in đậm / màu khác">
        </div>
      </div>

      <!-- description -->
      <div class="field">
        <label class="field__label" for="slide-description">Mô tả</label>
        <textarea id="slide-description" class="field__input" name="description"
          placeholder="Mô tả ngắn hiển thị trên slide" rows="2"></textarea>
      </div>

      <!-- media_id (hidden) -->
      <input type="hidden" id="slide-media-id" name="media_id" value="">

      <!-- Media preview + picker -->
      <div class="field" data-field-required>
        <label class="field__label">Hình ảnh slide</label>
        <div id="slide-media-preview" class="slide-media-preview">
          <div class="slide-media-preview__empty" id="slide-media-empty">
            <i class="fa-solid fa-image"></i>
            <span>Chưa có ảnh</span>
          </div>
          <img id="slide-media-img" class="slide-media-preview__img" src="" alt="" hidden>
        </div>
        <button type="button" class="btn mt-2" data-variant="outline" data-size="sm" id="slide-change-media-btn"
          data-modal-trigger="#media-selector-modal">
          <i class="fa-solid fa-image"></i> Thay đổi
        </button>
      </div>

      <!-- CTA -->
      <div class="grid grid-cols-2 gap-4">
        <div class="field">
          <label class="field__label" for="slide-cta-label">Nhãn nút CTA</label>
          <input id="slide-cta-label" class="field__input" type="text" name="cta_label" placeholder="VD: Tìm hiểu thêm">
        </div>
        <div class="field">
          <label class="field__label" for="slide-cta-variant">Kiểu nút CTA</label>
          <select id="slide-cta-variant" class="field__input" name="cta_variant">
            <option value="primary">Primary</option>
            <option value="secondary">Secondary</option>
            <option value="outline">Outline</option>
          </select>
        </div>
      </div>
      <div class="field">
        <label class="field__label" for="slide-cta-url">URL nút CTA</label>
        <input id="slide-cta-url" class="field__input" type="text" name="cta_url"
          placeholder="https://... hoặc /duong-dan">
      </div>

      <!-- use_custom_html toggle -->
      <div class="field" data-orientation="horizontal">
        <label class="field__label" for="slide-use-custom-html">Dùng HTML tuỳ chỉnh</label>
        <input id="slide-use-custom-html" type="checkbox" class="field__input" name="use_custom_html" value="1">
      </div>

      <!-- custom_html (hidden by default) -->
      <div class="field" id="slide-custom-html-field" style="display: none;">
        <label class="field__label" for="slide-custom-html">Custom HTML</label>
        <textarea id="slide-custom-html" class="field__input field__input--mono" name="custom_html"
          placeholder="<div>Nội dung HTML tuỳ chỉnh...</div>" rows="4" spellcheck="false"></textarea>
        <p class="field__description">Khi bật, nội dung HTML này sẽ thay thế title/description mặc định.</p>
      </div>
    </div>
  </form>

  <div class="modal__footer flex justify-between items-center">
    <div>
      <button id="slide-delete-btn" type="button" data-variant="destructive" data-size="lg"
        class="btn hidden">Xóa</button>
    </div>
    <div class="flex gap-2 ml-auto">
      <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
      <button id="slide-save-btn" data-variant="primary" data-size="lg" class="btn" type="button">Lưu</button>
    </div>
  </div>

  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<!-- ── Confirm Delete Slide Modal ── -->
<div class="modal" id="slide-delete-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận xóa Slide</h2>
    <p class="modal__description">Bạn có chắc chắn muốn xóa slide này khỏi danh sách tạo?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="slide-delete-confirm-btn" data-variant="destructive" data-size="lg" class="btn" type="button">Xác nhận
      xóa</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const mainForm = document.querySelector('#carousel-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const nameInput = document.querySelector('#name');
    const slugInput = document.querySelector('#slug');

    // Auto-slug normalization
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

    // Submit main form
    confirmBtn.addEventListener('click', () => {
      mainForm.submit();
    });

    // ── SLIDES LOCAL STATE MANAGEMENT ──────────────────────────────────────
    let localSlides = [];
    const slidesContainer = document.querySelector('#slides-container');
    const slidesEmptyHint = document.querySelector('#slides-empty-hint');
    const slidesInputsContainer = document.querySelector('#slides-inputs-container');

    const slideModal = document.querySelector('#slide-modal');
    const slideForm = slideModal.querySelector('#slide-form');
    const slideModalTitle = slideModal.querySelector('#slide-modal-title');
    const slideUseCustomHtml = slideModal.querySelector('#slide-use-custom-html');
    const slideCustomHtmlField = slideModal.querySelector('#slide-custom-html-field');
    const slideDeleteBtn = slideModal.querySelector('#slide-delete-btn');
    const slideSaveBtn = slideModal.querySelector('#slide-save-btn');

    const slideDeleteConfirmModal = document.querySelector('#slide-delete-confirm-modal');
    const confirmDeleteBtn = slideDeleteConfirmModal.querySelector('#slide-delete-confirm-btn');

    // Utility: HTML escape
    function escapeHtml(str) {
      if (!str) return '';
      return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    // Toggle Custom HTML field in modal
    slideUseCustomHtml.addEventListener('change', function () {
      slideCustomHtmlField.style.display = this.checked ? 'flex' : 'none';
    });

    // Reset slide modal form
    function resetSlideForm() {
      slideForm.reset();
      slideCustomHtmlField.style.display = 'none';
      slideDeleteBtn.classList.add('hidden');
      delete slideModal.dataset.editingTempId;

      slideForm.querySelector('#slide-media-id').value = '';
      document.querySelector('#slide-media-img').hidden = true;
      document.querySelector('#slide-media-img').src = '';
      document.querySelector('#slide-media-empty').hidden = false;
    }

    // Open modal to add slide
    document.querySelector('#add-item-btn').addEventListener('click', () => {
      resetSlideForm();
      slideModalTitle.textContent = 'Thêm slide mới';
      modalHandler.open('#slide-modal');
    });

    // Render list visual DOM and hidden form inputs
    function renderSlides() {
      // 1. Clear old content (keep empty hint template ready if needed)
      const visualItems = slidesContainer.querySelectorAll('.slide-item');
      visualItems.forEach(el => el.remove());

      if (localSlides.length === 0) {
        slidesEmptyHint.style.display = 'flex';
      } else {
        slidesEmptyHint.style.display = 'none';
      }

      // 2. Clear hidden inputs
      slidesInputsContainer.innerHTML = '';

      // 3. Rebuild visual DOM and inputs
      localSlides.forEach((slide, index) => {
        // A. Visual Card
        const card = document.createElement('div');
        card.className = 'slide-item flex items-center p-2 border rounded-md gap-2 shadow-sm hover-lift';
        card.dataset.tempId = slide.tempId;
        card.setAttribute('data-dnd-draggable', '');

        const imgHtml = slide.media_url
          ? `<img class="slide-item__img" src="${escapeHtml(slide.media_url)}" alt="">`
          : `<div class="slide-item__img"><div class="w-full h-full flex justify-center items-center gap-1"><i class="fa-solid fa-image"></i>N/A</div></div>`;

        card.innerHTML = `
          <div class="drag-handle shrink-0 flex flex-col cursor-grab">
            <i class="fa-solid fa-grip-vertical"></i>
          </div>
          <div class="flex-1 flex gap-2 items-center">
            ${imgHtml}
            <span class="flex-1 w-full font-medium text-sm">
              ${escapeHtml(slide.title || '')} 
              <span class="font-bold">${escapeHtml(slide.title_highlight || '')}</span>
            </span>
            <div>
              <span class="badge" data-variant="${slide.is_active ? 'primary' : 'secondary'}">
                ${slide.is_active ? 'Hiển thị' : 'Đã ẩn'}
              </span>
              <button type="button" class="btn ml-2 edit-slide-btn" data-variant="outline" data-size="md">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>
        `;

        // Attach edit click handler
        card.querySelector('.edit-slide-btn').addEventListener('click', () => {
          resetSlideForm();
          slideModalTitle.textContent = 'Chỉnh sửa slide';
          slideModal.dataset.editingTempId = slide.tempId;

          // Populate fields
          slideForm.querySelector('#slide-is-active').checked = Boolean(slide.is_active);
          slideForm.querySelector('#slide-title').value = slide.title || '';
          slideForm.querySelector('#slide-title-highlight').value = slide.title_highlight || '';
          slideForm.querySelector('#slide-description').value = slide.description || '';
          slideForm.querySelector('#slide-media-id').value = slide.media_id || '';
          slideForm.querySelector('#slide-cta-label').value = slide.cta_label || '';
          slideForm.querySelector('#slide-cta-variant').value = slide.cta_variant || 'primary';
          slideForm.querySelector('#slide-cta-url').value = slide.cta_url || '';

          const slideImg = document.querySelector('#slide-media-img');
          const slideEmpty = document.querySelector('#slide-media-empty');
          if (slide.media_url) {
            slideImg.src = slide.media_url;
            slideImg.alt = slide.media_alt || '';
            slideImg.hidden = false;
            slideEmpty.hidden = true;
          } else {
            slideImg.hidden = true;
            slideImg.src = '';
            slideEmpty.hidden = false;
          }

          const useCustom = Boolean(slide.use_custom_html);
          slideUseCustomHtml.checked = useCustom;
          slideCustomHtmlField.style.display = useCustom ? 'flex' : 'none';
          slideForm.querySelector('#slide-custom-html').value = slide.custom_html || '';

          slideDeleteBtn.classList.remove('hidden');
          modalHandler.open('#slide-modal');
        });

        slidesContainer.appendChild(card);

        const slideFields = [
          'title', 'title_highlight', 'description', 'media_id',
          'cta_label', 'cta_variant', 'cta_url', 'custom_html'
        ];

        slideFields.forEach(field => {
          const inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = `slides[${index}][${field}]`;
          inp.value = slide[field] || '';
          slidesInputsContainer.appendChild(inp);
        });

        // Booleans
        const activeInp = document.createElement('input');
        activeInp.type = 'hidden';
        activeInp.name = `slides[${index}][is_active]`;
        activeInp.value = slide.is_active ? '1' : '0';
        slidesInputsContainer.appendChild(activeInp);

        const customHtmlInp = document.createElement('input');
        customHtmlInp.type = 'hidden';
        customHtmlInp.name = `slides[${index}][use_custom_html]`;
        customHtmlInp.value = slide.use_custom_html ? '1' : '0';
        slidesInputsContainer.appendChild(customHtmlInp);

        // sort_order
        const sortInp = document.createElement('input');
        sortInp.type = 'hidden';
        sortInp.name = `slides[${index}][sort_order]`;
        sortInp.value = String(index + 1);
        slidesInputsContainer.appendChild(sortInp);
      });
    }

    // Modal Save Button Handler
    slideSaveBtn.addEventListener('click', () => {
      if (!slideForm.reportValidity()) return;

      const slideData = {
        is_active: slideForm.querySelector('#slide-is-active').checked ? 1 : 0,
        title: slideForm.querySelector('#slide-title').value.trim(),
        title_highlight: slideForm.querySelector('#slide-title-highlight').value.trim(),
        description: slideForm.querySelector('#slide-description').value.trim(),
        media_id: slideForm.querySelector('#slide-media-id').value,
        media_url: document.querySelector('#slide-media-img').src,
        media_alt: document.querySelector('#slide-media-img').alt,
        cta_label: slideForm.querySelector('#slide-cta-label').value.trim(),
        cta_variant: slideForm.querySelector('#slide-cta-variant').value,
        cta_url: slideForm.querySelector('#slide-cta-url').value.trim(),
        use_custom_html: slideUseCustomHtml.checked ? 1 : 0,
        custom_html: slideForm.querySelector('#slide-custom-html').value.trim()
      };

      const editingTempId = slideModal.dataset.editingTempId;
      if (editingTempId) {
        // Edit existing
        const idx = localSlides.findIndex(s => s.tempId === editingTempId);
        if (idx !== -1) {
          localSlides[idx] = { ...localSlides[idx], ...slideData };
        }
      } else {
        // Add new
        slideData.tempId = 'slide_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localSlides.push(slideData);
      }

      renderSlides();
      modalHandler.close('#slide-modal');
    });

    // Modal Delete Button Handler
    slideDeleteBtn.addEventListener('click', () => {
      const editingTempId = slideModal.dataset.editingTempId;
      if (!editingTempId) return;

      confirmDeleteBtn.onclick = () => {
        localSlides = localSlides.filter(s => s.tempId !== editingTempId);
        renderSlides();
        modalHandler.close('#slide-delete-confirm-modal');
        modalHandler.close('#slide-modal');
      };
      modalHandler.open('#slide-delete-confirm-modal');
    });

    // Media Selector Modal Handler
    document.querySelector('#media-selector-modal')?.addEventListener('msm:submit', (e) => {
      const { media, close } = e.detail;

      // Cập nhật hidden input
      slideForm.querySelector('#slide-media-id').value = media.id;

      // Cập nhật thumbnail preview
      const slideImg = document.querySelector('#slide-media-img');
      const slideEmpty = document.querySelector('#slide-media-empty');
      const mediaUrl = `<?= url('public/media/') ?>/${media.file_path}`;

      slideImg.src = mediaUrl;
      slideImg.alt = media.alt_text || '';
      slideImg.hidden = false;
      slideEmpty.hidden = true;

      close();
    });

    if (slidesContainer) {
      new DnD(slidesContainer, {
        animation: 150,
        group: "slides",
        handle: '.drag-handle',
        ghostClass: 'dnd-ghost',
        chosenClass: 'dnd-chosen',
        onEnd: () => {
          const reorderedTempIds = Array.from(slidesContainer.querySelectorAll('.slide-item')).map(el => el.dataset.tempId);

          const sortedSlides = [];
          reorderedTempIds.forEach(tempId => {
            const found = localSlides.find(s => s.tempId === tempId);
            if (found) sortedSlides.push(found);
          });

          localSlides = sortedSlides;
          renderSlides();
        }
      });
    }
  });
</script>