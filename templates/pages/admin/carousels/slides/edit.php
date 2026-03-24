<?php
$errors = request()->getErrors() ?? [];
$old_input = request()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<div class="detail-panel card shadow">
  <div class="card__header flex justify-between items-start">
    <div>
      <div class="card__title">
        <h6>
          Sửa Slide
          <span class="font-bold">#<?= htmlspecialchars($slide->id ?? '') ?></span>
        </h6>
      </div>
      <div class="card__description">
        Thuộc Carousel: <strong><?= htmlspecialchars($carousel->name) ?></strong>
      </div>
    </div>
  </div>

  <div class="card__content">
    <?php include BASE_PATH . '/templates/components/flash_alert.php'; ?>

    <form id="slide-edit-form" method="POST"
      action="<?= url('admin/carousels/' . $carousel->id . '/slides/' . $slide->id) ?>">
      <div class="field-group">

        <div class="field" data-field-required>
          <label for="title">Tiêu đề chính</label>
          <input id="title" class="field__input" type="text" name="title"
            value="<?= htmlspecialchars($old_input['title'] ?? $slide->title ?? '') ?>"
            placeholder="VD: Khuyến mãi mùa hè">
        </div>

        <div class="field">
          <label for="title_highlight">Tiêu đề nổi bật (Highlight)</label>
          <input id="title_highlight" class="field__input" type="text" name="title_highlight"
            value="<?= htmlspecialchars($old_input['title_highlight'] ?? $slide->title_highlight ?? '') ?>"
            placeholder="Đoạn text nhấn mạnh bên dưới tiêu đề">
        </div>

        <div class="field">
          <label for="description">Mô tả (Description)</label>
          <textarea id="description" class="field__input" name="description" rows="3"
            placeholder="Nhập đoạn mô tả ngắn cho slide..."><?= htmlspecialchars($old_input['description'] ?? $slide->description ?? '') ?></textarea>
        </div>

        <div class="field" data-field-required>
          <label for="image_path">Đường dẫn hình ảnh</label>
          <input id="image_path" class="field__input" type="text" name="image_path"
            value="<?= htmlspecialchars($old_input['image_path'] ?? $slide->image_path ?? '') ?>">

          <?php if (!empty($slide->image_path)): ?>
            <div class="mt-2">
              <img src="<?= url($slide->image_path) ?>" alt="Preview" class="rounded object-cover border"
                style="max-height: 120px;">
            </div>
          <?php endif; ?>
        </div>

        <div class="field">
          <label for="image_alt">Alt text của hình ảnh (SEO)</label>
          <input id="image_alt" class="field__input" type="text" name="image_alt"
            value="<?= htmlspecialchars($old_input['image_alt'] ?? $slide->image_alt ?? '') ?>"
            placeholder="Mô tả hình ảnh cho SEO">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="field col-span-1 md:col-span-1">
            <label for="cta_label">Nút bấm (Label)</label>
            <input id="cta_label" class="field__input" type="text" name="cta_label"
              value="<?= htmlspecialchars($old_input['cta_label'] ?? $slide->cta_label ?? '') ?>"
              placeholder="Để trống nếu ẩn nút">
          </div>

          <div class="field col-span-1 md:col-span-1">
            <label for="cta_url">Đường dẫn Nút bấm (URL)</label>
            <input id="cta_url" class="field__input" type="text" name="cta_url"
              value="<?= htmlspecialchars($old_input['cta_url'] ?? $slide->cta_url ?? '') ?>">
          </div>

          <div class="field col-span-1 md:col-span-1">
            <label for="cta_variant">Giao diện nút (Variant)</label>
            <?php $currentVariant = $old_input['cta_variant'] ?? $slide->cta_variant ?? 'primary'; ?>
            <select id="cta_variant" class="field__input" name="cta_variant">
              <option value="primary" <?= $currentVariant === 'primary' ? 'selected' : '' ?>>Primary (Nổi bật)</option>
              <option value="secondary" <?= $currentVariant === 'secondary' ? 'selected' : '' ?>>Secondary (Phụ)</option>
            </select>
          </div>
        </div>

        <div class="field mt-4">
          <label class="field__toggle">
            <?php $useCustomHtml = isset($old_input['use_custom_html']) ? !empty($old_input['use_custom_html']) : $slide->use_custom_html; ?>
            <input type="checkbox" name="use_custom_html" id="use_custom_html" value="1" <?= $useCustomHtml ? 'checked' : '' ?>>
            <span class="field__toggle-track"></span>
            <span class="field__toggle-label font-medium text-blue-600">Sử dụng Custom HTML (Ghi đè hiển thị mặc
              định)</span>
          </label>
        </div>

        <div class="field">
          <label for="custom_html">Mã Custom HTML</label>
          <textarea id="custom_html" class="field__input font-mono text-sm bg-gray-50" name="custom_html" rows="5"
            placeholder="<h1>Khuyến mãi</h1><p>...</p>"><?= htmlspecialchars($old_input['custom_html'] ?? $slide->custom_html ?? '') ?></textarea>
        </div>

        <div class="field border-t pt-4">
          <label class="field__toggle">
            <?php $isActive = isset($old_input['is_active']) ? !empty($old_input['is_active']) : $slide->isActive(); ?>
            <input type="checkbox" name="is_active" id="is_active" value="1" <?= $isActive ? 'checked' : '' ?>>
            <span class="field__toggle-track"></span>
            <span class="field__toggle-label">Kích hoạt / Hiển thị Slide này</span>
          </label>
        </div>

      </div>
    </form>
  </div>

  <div class="card__footer">
    <button data-modal-trigger="#confirm-modal" type="button" data-variant="primary" data-size="lg" class="btn">
      Lưu thay đổi
    </button>
    <button data-modal-trigger="#delete-modal" type="button" data-variant="destructive" data-size="lg" class="btn">
      Xóa Slide
    </button>
  </div>
</div>

<form id="slide-delete-form" method="POST"
  action="<?= url('admin/carousels/' . $carousel->id . '/slides/delete/' . $slide->id) ?>"></form>

<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận chỉnh sửa</h2>
    <p class="modal__description">Bạn có chắc muốn lưu các thay đổi của slide này?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Lưu</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
      <path
        d="M55.1 73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L147.2 256 9.9 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192.5 301.3 329.9 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.8 256 375.1 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 147.2 118.6 9.9z" />
    </svg>
  </button>
</div>

<div class="modal" id="delete-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title text-red-600">Xóa Slide</h2>
    <p class="modal__description">Bạn có chắc chắn muốn xóa Slide này? Thao tác này không thể hoàn tác.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="delete-confirm-btn" data-variant="destructive" data-size="lg" class="btn" type="button">Xóa</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
      <path
        d="M55.1 73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L147.2 256 9.9 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192.5 301.3 329.9 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.8 256 375.1 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 147.2 118.6 9.9z" />
    </svg>
  </button>
</div>

<div class="modal-overlay" data-modal-close></div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const editForm = document.querySelector('#slide-edit-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    if (confirmBtn) confirmBtn.addEventListener('click', () => editForm.submit());

    const deleteForm = document.querySelector('#slide-delete-form');
    const deleteConfirmBtn = document.querySelector('#delete-confirm-btn');
    if (deleteConfirmBtn) deleteConfirmBtn.addEventListener('click', () => deleteForm.submit());
  });
</script>