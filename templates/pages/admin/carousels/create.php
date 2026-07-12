<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>


<!-- Modal select media -->
<?php require_once(BASE_PATH . '/templates/components/media_selector_modal.php'); ?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Thêm carousel mới</h2>
<p class="title-wrapper__description">Điền thông tin carousel và các slide bên dưới</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("admin/carousels") ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="button" data-variant="primary" data-size="lg"
  class="btn">

  Thêm
</button>
<?php $layout->end() ?>
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
    <h3 class="modal__title">Xác nhận tạo Carousel</h3>
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
    <h3 class="modal__title">Xác nhận xóa Slide</h3>
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

<?php $layout->start("scripts") ?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>
<script>window.__carouselCreate__ = { mediaBaseUrl: <?= json_encode(url('public/media')) ?> };</script>
<script src="<?= url('public/js/pages/admin/carousels/create.js') ?>" type="module"></script>
<?php $layout->end() ?>
