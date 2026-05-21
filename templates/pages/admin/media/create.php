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
      <h2 class="title text-2xl font-semibold">Thêm mới media</h2>
      <p>Upload & điền thông tin media mới vào các trường dưới đây</p>
    </div>
    <div class="flex gap-2">
      <a href="<?= request()->previous(fallback: 'admin/media') ?>"
         data-variant="outline" data-size="lg" class="btn">
        <i class="fa-solid fa-chevron-left"></i> Quay lại
      </a>
      <button data-modal-trigger="#confirm-modal" type="button" data-variant="primary" data-size="lg" class="btn">
        Lưu
      </button>
    </div>
  </div>
</div>

<div class="detail-layout">
  <div class="detail-layout__main flex-1">
    <div class="card shadow">

      <div class="card__header">
        <legend class="card__title field__legend">Thêm media</legend>
        <p class="card__description field__description">Upload file ảnh hoặc video từ máy tính</p>
      </div>

      <hr class="separator">

      <div class="card__content">
        <form id="media-form" method="POST"
              action="<?= url('admin/media') ?>"
              enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="field-group">

            <div class="empty media-upload-zone" id="upload-zone">
              <input type="file" id="file" name="file" accept="image/*,video/*" hidden required>
              <div class="empty__header">
                <div class="empty__media">
                  <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div class="empty__title">Upload Media</div>
                <div class="empty__description">
                  Chưa có media nào. Upload media đầu tiên. Hỗ trợ: JPG, PNG, GIF, WebP, MP4, MOV, AVI…
                </div>
              </div>
              <div class="empty__content">
                <span>Kéo thả hoặc</span>
                <button type="button" class="btn" data-variant="primary" data-size="md" id="upload-trigger">
                Chọn file
                </button>
              </div>
            </div>

            <div class="media-upload-zone__preview" id="upload-preview" hidden>
              <div class="media-upload-zone__preview-wrapper" id="preview-media"></div>

              <div class="media-upload-zone__preview-actions">
                <button type="button" class="btn" data-variant="outline" data-size="sm" id="upload-change">
                  <i class="fa-solid fa-arrow-up-from-bracket"></i> Đổi file
                </button>
              </div>
            </div>

            <div class="field" data-field-required>
              <label class="field__label" for="file_name">Tên file</label>
              <input id="file_name" class="field__input" type="text"
                     name="file_name" placeholder="Tự động điền khi chọn file"
                     value="<?= htmlspecialchars($old_input['file_name'] ?? '') ?>">
            </div>

            <div class="field">
              <label class="field__label" for="alt_text">Alt text</label>
              <input id="alt_text" class="field__input" type="text"
                     name="alt_text" placeholder="Mô tả nội dung media (SEO, accessibility)"
                     value="<?= htmlspecialchars($old_input['alt_text'] ?? '') ?>">
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
    <p class="modal__description">Bạn có chắc muốn thêm media này?</p>
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
  const fileInput     = document.querySelector('#file');
  const uploadTrigger = document.querySelector('#upload-trigger');
  const uploadChange  = document.querySelector('#upload-change');
  const uploadZone    = document.querySelector('#upload-zone');
  const uploadPreview = document.querySelector('#upload-preview');
  const previewMedia  = document.querySelector('#preview-media');
  const fileNameInput = document.querySelector('#file_name');
  const form          = document.querySelector('#media-form');
  const confirmBtn    = document.querySelector('#confirm-btn');

  uploadTrigger.addEventListener('click', () => fileInput.click());
  uploadChange.addEventListener('click',  () => fileInput.click());

  uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('media-upload-zone--draging');
  });

  ['dragleave', 'dragend'].forEach(ev =>
    uploadZone.addEventListener(ev, () =>
      uploadZone.classList.remove('media-upload-zone--draging')
    )
  );

  uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('media-upload-zone--draging');

    const file = e.dataTransfer.files[0];
    if (!file) return;

    const dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;

    handleFile(file);
  });

  fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    if (file) handleFile(file);
  });

  function handleFile(file) {
    renderPreview(file);
    autoFillName(file.name);
  }

  function renderPreview(file) {
    const objectUrl = URL.createObjectURL(file);

    previewMedia.innerHTML = '';

    if (file.type.startsWith('image/')) {
      const img = document.createElement('img');
      img.src = objectUrl;
      img.className = 'upload-preview__img';
      img.alt = 'Preview';
      img.onload = () => URL.revokeObjectURL(objectUrl);
      previewMedia.appendChild(img);

    } else if (file.type.startsWith('video/')) {
      const video = document.createElement('video');
      video.src = objectUrl;
      video.className = 'upload-preview__video';
      video.controls = true;
      video.muted = true;
      video.onloadedmetadata = () => URL.revokeObjectURL(objectUrl);
      previewMedia.appendChild(video);

    } else {
      previewMedia.innerHTML = `<p class="upload-preview__unknown">
        <i class="fa-solid fa-file"></i> ${file.name}
      </p>`;
    }

    uploadZone.hidden = true;
    uploadPreview.hidden = false;
  }

  function autoFillName(rawFileName) {
    const nameWithoutExt = rawFileName.replace(/\.[^.]+$/, '');
    if (!fileNameInput.value.trim()) {
      fileNameInput.value = nameWithoutExt;
    }
  }

  // ── Submit qua confirm modal ─────────────────────────────────────────────
  confirmBtn.addEventListener('click', () => form.submit());

});
</script>