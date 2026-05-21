<?php
$errors    = request()->session()->getErrors()    ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__    = <?= json_encode($old_input) ?>;
</script>

<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Media
        <?= '#' . $media->id; ?>
      </h2>
      <p>Upload & điền thông tin media mới vào các trường dưới đây</p>
    </div>
    <div class="flex gap-2">
      <a href="<?= request()->previous(fallback: 'admin/media') ?>"
         data-variant="outline" data-size="lg" class="btn">
        <i class="fa-solid fa-chevron-left"></i> Quay lại
      </a>
      <button data-modal-trigger="#confirm-modal" type="button" data-variant="primary" data-size="lg" class="btn">
        Lưu thay đổi
      </button>
    </div>
  </div>
</div>

<div class="detail-layout">
  <div class="detail-layout__main flex-1">
    <div class="card shadow">

      <div class="card__header">
        <legend class="card__title field__legend">Sửa media</legend>
        <p class="card__description field__description">Upload file ảnh hoặc video từ máy tính</p>
      </div>

      <hr class="separator">

      <div class="card__content">
        <form id="media-form" method="POST"
              action="<?= url('admin/media/' . $media->id) ?>"
              enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="field-group">
            <div class="media-upload-zone__preview" id="upload-preview" hidden>
              <div class="media-upload-zone__preview-wrapper" id="preview-media"></div>
            </div>
  
            <div class="field" data-field-required>
              <label class="field__label" for="file_name">Tên file</label>
              <input id="file_name" class="field__input" type="text"
                     name="file_name" placeholder="Tự động điền khi chọn file"
                     value="<?= htmlspecialchars($old_input['file_name'] ?? $media->file_name) ?>">
            </div>
  
            <div class="field">
              <label class="field__label" for="alt_text">Alt text</label>
              <input id="alt_text" class="field__input" type="text"
                     name="alt_text" placeholder="Mô tả nội dung media (SEO, accessibility)"
                     value="<?= htmlspecialchars($old_input['alt_text'] ?? $media->alt_text) ?>">
            </div>
          </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ── Confirm modal ── -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận lưu</h2>
    <p class="modal__description">Bạn có chắc muốn lưu thay đổi cho media này?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg"
            class="btn" type="button">Hủy</button>
    <button id="confirm-btn" data-variant="primary" data-size="lg"
            class="btn" type="button">Lưu</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const uploadPreview   = document.querySelector('#upload-preview');
  const previewMedia    = document.querySelector('#preview-media');
  const fileNameInput   = document.querySelector('#file_name');
  const form            = document.querySelector('#media-form');
  const confirmBtn      = document.querySelector('#confirm-btn');
  const existingMediaUrl = <?= json_encode($media->file_path ? url('public/img/' . $media->file_path) : null) ?>;
  const existingMimeType = <?= json_encode($media->mime_type ?? '') ?>;

  if (existingMediaUrl && existingMimeType) {
    renderExistingPreview(existingMediaUrl, existingMimeType);
  }

  function renderExistingPreview(url, mimeType) {
    previewMedia.innerHTML = '';

    if (mimeType.startsWith('image/')) {
      const img = document.createElement('img');
      img.src = url;
      img.className = 'upload-preview__img';
      img.alt = 'Preview';
      previewMedia.appendChild(img);

    } else if (mimeType.startsWith('video/')) {
      const video = document.createElement('video');
      video.src = url;
      video.className = 'upload-preview__video';
      video.controls = true;
      video.muted = true;
      previewMedia.appendChild(video);

    } else {
      const filename = url.substring(url.lastIndexOf('/') + 1);
      previewMedia.innerHTML = `<p class="upload-preview__unknown">
        <i class="fa-solid fa-file"></i> ${filename}
      </p>`;
    }

    uploadPreview.hidden = false;
  }

  // ── Submit qua confirm modal ─────────────────────────────────────────────
  confirmBtn.addEventListener('click', () => form.submit());

});
</script>