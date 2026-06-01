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

<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Carousel
        <?= ' #' . htmlspecialchars($carousel->id) ?>
      </h2>
      <p>Xem chi tiết và chỉnh sửa thông tin carousel.</p>
    </div>
    <div class="flex gap-2">
      <div>
        <a href="<?= url('admin/carousels') ?>" data-variant="outline" data-size="lg"
          class="btn">
          <i class="fa-solid fa-chevron-left"></i>
          Quay lại
        </a>
      </div>
      <div>
        <button data-modal-trigger="#confirm-modal" id="edit-submit-btn" type="submit" data-variant="primary"
          data-size="lg" class="btn">
          Lưu
        </button>
      </div>
    </div>
  </div>
</div>

<div class="detail-layout">
  <div class="detail-layout__main flex-1">
    <!-- Carousel Slides Card -->
    <div class="card shadow">
      <div class="card__header">
        <legend class="card__title field__legend">Các slide trong Carousel</legend>
        <p class="card__description field__description">Kéo thả để sắp xếp thứ tự hiển thị của các slide.</p>
        <button class="btn card__action" type="button" id="add-item-btn" data-modal-trigger="#slide-modal" data-variant="outline" data-size="lg">
          <i class="fa-solid fa-plus"></i>
          Thêm slide
        </button>
      </div>
      <hr class="separator">
      <div class="card__content">
        <div id="slides-container" class="space-y-2" data-id="<?= $carousel->id ?>">

        <?php if (!empty($carousel->slides)): ?>
          <?php foreach ($carousel->slides as $index => $slide): ?>
            <div class="slide-item flex items-center p-2 border rounded-md gap-2 shadow-sm hover-lift" data-dnd-draggable data-id="<?= $slide->id ?>">
              <div class="drag-handle shrink-0 flex flex-col">
                <i class="fa-solid fa-grip-vertical"></i>
              </div>

              <div class="flex-1 flex gap-2 items-center">
                <?php if (!empty($slide->media->file_path)): ?>
                  <img class="slide-item__img" src="<?= url('public/media/' . $slide->media->file_path) ?>" alt="">
                <?php else: ?>
                  <div class="slide-item__img">
                    <div class="w-full h-full flex justify-center items-center gap-1">
                        <i class="fa-solid fa-image"></i>
                        N/A
                    </div>
                  </div>
                <?php endif; ?>

                <span class="flex-1 w-full font-medium text-sm">
                  <?= htmlspecialchars($slide->title ?? '') ?>
                  <?= htmlspecialchars($slide->title_highlight ?? '') ?>
                </span>

                <div>
                  <?php if ($slide->isActive()): ?>
                    <span class="badge" data-variant="primary">Hiển thị</span>
                  <?php else: ?>
                    <span class="badge" data-variant="secondary">Đã ẩn</span>
                  <?php endif; ?>

                  <button type="button" class="btn ml-2 edit-slide-btn" data-variant="outline" data-size="md"
                    data-modal-trigger="#slide-modal"
                    data-slide='<?= json_encode([
                      'id'             => $slide->id,
                      'title'          => $slide->title,
                      'title_highlight' => $slide->title_highlight,
                      'description'    => $slide->description,
                      'media_id'       => $slide->media_id,
                      'media_url'      => !empty($slide->media->file_path) ? url('public/media/' . $slide->media->file_path) : null,
                      'media_alt'      => $slide->media->alt_text ?? null,
                      'cta_label'      => $slide->cta_label,
                      'cta_variant'    => $slide->cta_variant,
                      'cta_url'        => $slide->cta_url,
                      'use_custom_html' => $slide->use_custom_html,
                      'custom_html'    => $slide->custom_html,
                      'is_active'      => $slide->is_active,
                    ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                    <fa class="fa-solid fa-eye"></fa>
                  </button>
                </div>
              </div>
            </div>
        <?php endforeach; ?>
        <?php else: ?>
          <div class="empty">
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
        <?php endif; ?>
      </div> <!-- close slides-container -->
      </div>
    </div>
  </div>

  <div class="detail-layout__sidebar">
    <!-- Info Card -->
    <div class="card shadow">
      <div class="card__header">
        <legend class="card__title field__legend">Thông tin Carousel</legend>
      </div>
      <hr class="separator">
      <div class="card__content">
        <form id="carousel-edit-form" method="POST" action="<?= url('admin/carousels/' . $carousel->id) ?>">
          <?= csrf_field() ?>
          <input type="hidden" id="carousel-reorder-input" name="reorder" value="">
          <div class="field-group">
            <div class="field" data-field-required>
              <label class="field__label" for="name">Tên Carousel</label>
              <input id="name" class="field__input" type="text" name="name"
                value="<?= htmlspecialchars($old_input['name'] ?? $carousel->name ?? '') ?>">
            </div>

            <div class="field">
              <label class="field__label" for="slug">Đường dẫn (Slug)</label>
              <input id="slug" class="field__input" type="text" name="slug"
                value="<?= htmlspecialchars($old_input['slug'] ?? $carousel->slug ?? '') ?>">
            </div>

            <div class="field" data-orientation="horizontal">
              <label class="field__label" for="is_active">Kích hoạt carousel</label>
              <?php $isActive = isset($old_input['is_active']) ? !empty($old_input['is_active']) : $carousel->isActive(); ?>
              <input id="is_active" class="field__input" type="checkbox" name="is_active" value="1"
                <?= $isActive ? 'checked' : '' ?>>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Metadata -->
    <div class="metadata-card card shadow">
      <div class="card__header">
        <div class="card__title">
          Thông tin bản ghi
        </div>
      </div>
      <hr class="separator">
      <div class="card__content space-y-4">
        <dl class="flex justify-between">
          <dt>ID</dt>
          <dd><?= htmlspecialchars($carousel->id) ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Được tạo vào</dt>
          <dd><?= htmlspecialchars($carousel->created_at) ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Lần cuối cập nhật</dt>
          <dd><?= htmlspecialchars($carousel->updated_at ? $carousel->updated_at : "Không có") ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Trạng thái dữ liệu</dt>
          <dd>
            <?php if ($carousel->deleted_at): ?>
              <span class="badge" data-variant="destructive">
                Đã xóa
              </span>
            <?php else: ?>
              <span class="badge" data-variant="primary">Hoạt động</span>
            <?php endif; ?>
          </dd>
        </dl>
      </div>
    </div>
  </div>
</div>
<!-- ==========================================================================
     MODALS SECTION
     ========================================================================== -->

<!-- 1. Confirm Update Modal -->
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
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<!-- 2. Delete Carousel Modal -->
<div class="modal" id="delete-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xóa Carousel</h2>
    <p class="modal__description">Bạn có chắc chắn muốn xóa Carousel này? Các Slides cũng sẽ bị xóa.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="delete-confirm-btn" data-variant="destructive" data-size="lg" class="btn" type="button">Xóa</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<!-- 3. Slide CRUD Modal -->
<div class="modal detail-modal" id="slide-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title" id="slide-modal-title">Thêm slide mới</h2>
    <p class="modal__description">Điền thông tin slide bên dưới.</p>
  </div>
  <form id="slide-form" method="POST" action="">
    <?= csrf_field() ?>
    <div class="detail-modal__form space-y-4">
      <div class="field-group">
        <!-- is_active -->
        <div class="field" data-orientation="horizontal">
          <label class="field__label" for="slide-is-active">Kích hoạt slide</label>
          <input id="slide-is-active" type="checkbox" class="field__input" name="is_active" value="1" checked></input>
        </div>

        <!-- title + title_highlight -->
        <div class="grid grid-cols-2 gap-4">
          <div class="field" data-field-required>
            <label class="field__label">Tiêu đề</label>
            <input id="slide-title" class="field__input" type="text" name="title" placeholder="Tiêu đề chính của slide" required>
          </div>
          <div class="field">
            <label class="field__label">Tiêu đề nổi bật</label>
            <input id="slide-title-highlight" class="field__input" type="text" name="title_highlight" placeholder="Phần in đậm / màu khác">
          </div>
        </div>

        <!-- description -->
        <div class="field">
          <label class="field__label">Mô tả</label>
          <textarea id="slide-description" class="field__input" name="description" placeholder="Mô tả ngắn hiển thị trên slide" rows="2"></textarea>
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
          <button type="button" class="btn mt-2" data-variant="outline" data-size="sm"
                  id="slide-change-media-btn" data-modal-trigger="#media-selector-modal">
            <i class="fa-solid fa-image"></i> Thay đổi
          </button>
        </div>

        <!-- CTA -->
        <div class="grid grid-cols-2 gap-4">
          <div class="field">
            <label class="field__label">Nhãn nút CTA</label>
            <input id="slide-cta-label" class="field__input" type="text" name="cta_label" placeholder="VD: Tìm hiểu thêm">
          </div>
          <div class="field">
            <label class="field__label">Kiểu nút CTA</label>
            <button type="button" id="slide-cta-variant" class="select" data-select-id="slide-cta-variant" data-select-placeholder="Chọn"
                name="status" data-be-meta-key="status" role="listbox" data-select-default-value="primary"
                data-select-placeholder="Chọn">
                <div class="select__content">
                  <div class="select__item" data-select-value="primary">Primary</div>
                  <div class="select__item" data-select-value="secondary">Secondary</div>
                  <div class="select__item" data-select-value="outline">Outline</div>
                </div>
              </button>
          </div>
        </div>
        <div class="field">
          <label class="field__label">URL nút CTA</label>
          <input id="slide-cta-url" class="field__input" type="text" name="cta_url" placeholder="https://... hoặc /duong-dan">
        </div>

        <!-- use_custom_html toggle -->
        <div class="field" data-orientation="horizontal">
          <label class="field__label" for="slide-use-custom-html">Dùng HTML tuỳ chỉnh</label>
          <input id="slide-use-custom-html" type="checkbox" class="field__input" name="use_custom_html" value="1">
        </div>
        
        <!-- custom_html (ẩn mặc định) -->
        <div class="field" id="slide-custom-html-field" style="display: none;">
          <label class="field__label">Custom HTML</label>
          <textarea id="slide-custom-html" class="field__input field__input--mono" name="custom_html" placeholder="<div>Nội dung HTML tuỳ chỉnh...</div>" rows="4" spellcheck="false"></textarea>
          <p class="field__description">Khi bật, nội dung HTML này sẽ thay thế title/description mặc định.</p>
        </div>
      </div>
    </div>
  </form>

  <div class="modal__footer flex justify-between items-center">
    <div>
      <button id="slide-delete-btn" type="button" data-variant="destructive" data-size="lg" class="btn">Xóa</button>
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

<!-- 4. Confirm Delete Slide Modal (Stacked) -->
<div class="modal" id="slide-delete-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận xóa Slide</h2>
    <p class="modal__description">Bạn có chắc chắn muốn xóa slide này? Hành động này không thể hoàn tác.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="slide-delete-confirm-btn" data-variant="destructive" data-size="lg" class="btn" type="button">Xác nhận xóa</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<!-- Hidden Carousel Delete Form -->
<form id="carousel-delete-form" method="POST" action="<?= url('admin/carousels/delete/' . $carousel->id) ?>">
  <?= csrf_field() ?>
</form>

<!-- Hidden Slide Delete Form -->
<form id="slide-delete-form" method="POST" action="">
  <?= csrf_field() ?>
</form>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const editForm = document.querySelector('#carousel-edit-form');
    const confirmModal = document.querySelector('#confirm-modal');
    const confirmBtn = confirmModal?.querySelector('#confirm-modal-btn');
    if (confirmBtn) confirmBtn.addEventListener('click', () => editForm.submit());

    const deleteForm = document.querySelector('#carousel-delete-form');
    const deleteModal = document.querySelector('#delete-modal');
    const deleteConfirmBtn = deleteModal?.querySelector('#delete-confirm-btn');
    if (deleteConfirmBtn) deleteConfirmBtn.addEventListener('click', () => deleteForm.submit());

    const slidesContainer = document.querySelector('#slides-container');

    if (slidesContainer) {
      new DnD(slidesContainer, {
        animation: 150,
        group: "slides",
        handle: '.drag-handle',
        ghostClass: 'dnd-ghost',
        chosenClass: 'dnd-chosen',
        onEnd: e => {
          const ordered = Array.from(slidesContainer.querySelectorAll('.slide-item')).map(el => el.dataset.id); // array of slide IDs
          const reorderInput = document.querySelector('#carousel-reorder-input');
          if (reorderInput) {
            reorderInput.value = JSON.stringify(ordered);
          }
        }
      });
    }

    // Slide Modal Logic
    const slideModal = document.querySelector('#slide-modal');
    const slideForm = slideModal?.querySelector('#slide-form');
    const slideModalTitle = slideModal?.querySelector('#slide-modal-title');
    const slideCustomHtmlField = slideForm?.querySelector('#slide-custom-html-field');
    const slideUseCustomHtml = slideForm?.querySelector('#slide-use-custom-html');
    const slideDeleteBtn = slideModal?.querySelector('#slide-delete-btn');
    const slideDeleteForm = document.querySelector('#slide-delete-form');

    const slideAddBtn = document.querySelector('#add-item-btn');
    const slideDeleteConfirmModal = document.querySelector('#slide-delete-confirm-modal');
    const slideSaveBtn = slideModal?.querySelector('#slide-save-btn');
    const confirmDeleteBtn = slideDeleteConfirmModal?.querySelector('#slide-delete-confirm-btn');

    slideUseCustomHtml.addEventListener('change', function() {
      slideCustomHtmlField.style.display = this.checked ? 'flex' : 'none';
    });

    function resetSlideForm() {
      slideForm.reset();
      slideCustomHtmlField.style.display = 'none';
      slideDeleteBtn.classList.add('hidden');

      slideForm.querySelector('#slide-media-id').value = '';
      document.querySelector('#slide-media-img').hidden = true;
      document.querySelector('#slide-media-img').src = '';
      document.querySelector('#slide-media-empty').hidden = false;
    }

    // Thêm slide mới
    if (slideAddBtn) {
      slideAddBtn.addEventListener('click', () => {
        resetSlideForm();
        slideModalTitle.textContent = 'Thêm slide mới';
        slideForm.action = `<?= url('admin/carousels/' . $carousel->id . '/slides') ?>`;
        slideForm.querySelector('#slide-is-active').checked = true;
      });
    }

    // Sửa/Xem slide
    document.querySelectorAll('.edit-slide-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        resetSlideForm();
        const slide = JSON.parse(btn.dataset.slide);

        slideModalTitle.textContent = 'Chỉnh sửa slide';
        slideForm.action = `<?= url('admin/carousel-slides/') ?>` + slide.id;

        slideForm.querySelector('#slide-is-active').checked = Boolean(slide.is_active);
        slideForm.querySelector('#slide-title').value = slide.title || '';
        slideForm.querySelector('#slide-title-highlight').value = slide.title_highlight || '';
        slideForm.querySelector('#slide-description').value = slide.description || '';
        slideForm.querySelector('#slide-media-id').value = slide.media_id || '';
        slideForm.querySelector('#slide-cta-label').value = slide.cta_label || '';
        slideForm.querySelector('#slide-cta-variant').value = slide.cta_variant || 'primary';
        slideForm.querySelector('#slide-cta-url').value = slide.cta_url || '';

        const slideImg   = document.querySelector('#slide-media-img');
        const slideEmpty = document.querySelector('#slide-media-empty');
        if (slide.media_url) {
          slideImg.src    = slide.media_url;
          slideImg.alt    = slide.media_alt || '';
          slideImg.hidden = false;
          slideEmpty.hidden = true;
        } else {
          slideImg.hidden   = true;
          slideImg.src      = '';
          slideEmpty.hidden = false;
        }
        
        const useCustom = Boolean(slide.use_custom_html);
        slideUseCustomHtml.checked = useCustom;
        slideCustomHtmlField.style.display = useCustom ? 'flex' : 'none';
        slideForm.querySelector('#slide-custom-html').value = slide.custom_html || '';

        if (slideDeleteBtn) {
          slideDeleteBtn.classList.remove('hidden');
          slideDeleteBtn.onclick = () => {
            if (confirmDeleteBtn) {
              confirmDeleteBtn.onclick = () => {
                slideDeleteForm.action = `<?= url('admin/carousel-slides/') ?>` + slide.id + '/delete';
                slideDeleteForm.submit();
              };
            }
            modalHandler.open('#slide-delete-confirm-modal');
          };
        }
      });
    });

    document.querySelector('#media-selector-modal')?.addEventListener('msm:submit', (e) => {
      const { media, close } = e.detail;

      // Cập nhật hidden input
      slideForm.querySelector('#slide-media-id').value = media.id;

      // Cập nhật thumbnail preview
      const slideImg   = document.querySelector('#slide-media-img');
      const slideEmpty = document.querySelector('#slide-media-empty');
      const mediaUrl   = `<?= url('public/media/') ?>/${media.file_path}`;

      slideImg.src      = mediaUrl;
      slideImg.alt      = media.alt_text || '';
      slideImg.hidden   = false;
      slideEmpty.hidden = true;

      close();
    });

    if (slideSaveBtn) {
      slideSaveBtn.addEventListener('click', () => {
        if (slideForm.reportValidity()) {
          slideForm.submit();
        }
      });
    }
  });
</script>