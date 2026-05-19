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
      toast.<?= $flash['type'] ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>

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
        <a href="<?= request()->previous(fallback: 'admin/carousels') ?>" data-variant="outline" data-size="lg"
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

      <div class="card__content">
        <div id="slides-container" class="space-y-2" data-id="<?= $carousel->id ?>">

        <?php if (!empty($carousel->slides)): ?>
          <?php foreach ($carousel->slides as $index => $slide): ?>
            <div class="slide-item flex items-center p-2 border rounded-md gap-2 shadow-sm hover-lift" data-dnd-draggable data-id="<?= $slide->id ?>">
              <div class="drag-handle shrink-0 flex flex-col">
                <i class="fa-solid fa-grip-vertical"></i>
              </div>

              <div class="flex-1 flex gap-2 items-center">
                <?php if (!empty($slide->image_path)): ?>
                  <img class="slide-item__img" src="<?= url($slide->image_path) ?>" alt="">
                <?php else: ?>
                  <div class="slide-item__img">
                    <i class="fa-solid fa-image"></i>
                    N/A
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
                      'id' => $slide->id,
                      'title' => $slide->title,
                      'title_highlight' => $slide->title_highlight,
                      'description' => $slide->description,
                      'image_path' => $slide->image_path,
                      'image_alt' => $slide->image_alt,
                      'cta_label' => $slide->cta_label,
                      'cta_variant' => $slide->cta_variant,
                      'cta_url' => $slide->cta_url,
                      'use_custom_html' => $slide->use_custom_html,
                      'custom_html' => $slide->custom_html,
                      'is_active' => $slide->is_active,
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

        <!-- image_path + image_alt -->
        <div class="grid grid-cols-2 gap-4">
          <div class="field" data-field-required>
            <label class="field__label">Đường dẫn ảnh</label>
            <input id="slide-image-path" class="field__input" type="text" name="image_path" placeholder="/uploads/slides/anh.jpg" required>
          </div>
          <div class="field">
            <label class="field__label">Alt text (ảnh)</label>
            <input id="slide-image-alt" class="field__input" type="text" name="image_alt" placeholder="Mô tả ảnh cho SEO / accessibility">
          </div>
        </div>

        <!-- CTA -->
        <div class="grid grid-cols-2 gap-4">
          <div class="field">
            <label class="field__label">Nhãn nút CTA</label>
            <input id="slide-cta-label" class="field__input" type="text" name="cta_label" placeholder="VD: Tìm hiểu thêm">
          </div>
          <div class="field">
            <label class="field__label">Kiểu nút CTA</label>
            <select id="slide-cta-variant" class="field__input" name="cta_variant">
              <option value="primary">Primary</option>
              <option value="secondary">Secondary</option>
              <option value="outline">Outline</option>
            </select>
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
    <button id="slide-delete-btn" type="button" data-variant="destructive" data-size="lg" class="btn">Xóa</button>
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

    // DnD initialization
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

    const slideDeleteConfirmModal = document.querySelector('#slide-delete-confirm-modal');
    const confirmDeleteBtn = slideDeleteConfirmModal?.querySelector('#slide-delete-confirm-btn');

    // Toggle Custom HTML field display
    slideUseCustomHtml.addEventListener('change', function() {
      slideCustomHtmlField.style.display = this.checked ? 'flex' : 'none';
    });

    // Function to reset Form
    function resetSlideForm() {
      slideForm.reset();
      slideCustomHtmlField.style.display = 'none';
      slideDeleteBtn.classList.add('hidden');
    }

    // Add Button Click Handler
    const addSlideBtn = document.querySelector('#add-item-btn');
    if (addSlideBtn) {
      addSlideBtn.addEventListener('click', () => {
        resetSlideForm();
        slideModalTitle.textContent = 'Thêm slide mới';
        slideForm.action = `<?= url('admin/carousels/' . $carousel->id . '/slides') ?>`;
        slideForm.querySelector('#slide-is-active').checked = true;
      });
    }

    // Edit Buttons Click Handler
    document.querySelectorAll('.edit-slide-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        resetSlideForm();
        const slide = JSON.parse(btn.dataset.slide);

        slideModalTitle.textContent = 'Chỉnh sửa slide';
        slideForm.action = `<?= url('admin/carousels/' . $carousel->id . '/slides/') ?>` + slide.id;

        // Populate fields
        slideForm.querySelector('#slide-is-active').checked = Boolean(slide.is_active);
        slideForm.querySelector('#slide-title').value = slide.title || '';
        slideForm.querySelector('#slide-title-highlight').value = slide.title_highlight || '';
        slideForm.querySelector('#slide-description').value = slide.description || '';
        slideForm.querySelector('#slide-image-path').value = slide.image_path || '';
        slideForm.querySelector('#slide-image-alt').value = slide.image_alt || '';
        slideForm.querySelector('#slide-cta-label').value = slide.cta_label || '';
        slideForm.querySelector('#slide-cta-variant').value = slide.cta_variant || 'primary';
        slideForm.querySelector('#slide-cta-url').value = slide.cta_url || '';
        
        const useCustom = Boolean(slide.use_custom_html);
        slideUseCustomHtml.checked = useCustom;
        slideCustomHtmlField.style.display = useCustom ? 'flex' : 'none';
        slideForm.querySelector('#slide-custom-html').value = slide.custom_html || '';

        // Show delete button
        slideDeleteBtn.classList.remove('hidden');
        slideDeleteBtn.onclick = () => {
          if (confirmDeleteBtn) {
            confirmDeleteBtn.onclick = () => {
              slideDeleteForm.action = `<?= url('admin/carousels/' . $carousel->id . '/slides/delete/') ?>` + slide.id;
              slideDeleteForm.submit();
            };
          }
          modalHandler.open('#slide-delete-confirm-modal');
        };
      });
    });

    // Handle slide form submit when save button is clicked
    const slideSaveBtn = slideModal?.querySelector('#slide-save-btn');
    if (slideSaveBtn) {
      slideSaveBtn.addEventListener('click', () => {
        // Trigger standard browser validation since submit() ignores it on button clicks
        if (slideForm.reportValidity()) {
          slideForm.submit();
        }
      });
    }
  });
</script>