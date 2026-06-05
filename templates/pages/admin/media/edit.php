<?php
$errors    = request()->session()->getErrors()    ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
$max_mb    = (int) ($_ENV['MAX_UPLOAD_SIZE'] ?? 5);
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__    = <?= json_encode($old_input) ?>;
</script>

<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Media <?= '#' . $media->id ?>
      </h2>
      <p>Chỉnh sửa thông tin media nếu cần</p>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/media') ?>"
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
        <p class="card__description field__description">Cập nhật thông tin media bên dưới (Không thể thay thế file media)</p>
      </div>

      <hr class="separator">

      <div class="card__content">
        <form id="media-form" method="POST"
              action="<?= url('admin/media/' . $media->id) ?>"
              enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="field-group">

            <div class="media-upload-zone"
                 id="media-edit-upload-zone"
                 role="button"
                 tabindex="0"
                 aria-label="Khu vực kéo thả file"
                 data-mu-zone
                 data-mu-max-bytes="<?= $max_mb * 1024 * 1024 ?>"
                 <?php if (!empty($media->file_path)): ?>
                   data-mu-import-url="<?= htmlspecialchars(url('public/media/'.$media->file_path)) ?>"
                   data-mu-import-name="<?= htmlspecialchars($media->file_name ?? basename($media->file_path)) ?>"
                   data-mu-import-mime="<?= htmlspecialchars($media->mime_type ?? '') ?>"
                   data-mu-import-size="<?= (int) ($media->file_size ?? 0) ?>"
                 <?php endif ?>
            >

              <input type="file"
                     name="file"
                     accept="image/*,video/*,application/pdf,.doc,.docx,.txt"
                     hidden
                     data-mu-input>

              <div class="empty media-upload-zone-content" data-mu-empty>
                <div class="empty__header">
                  <div class="empty__media">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                  </div>
                  <div class="empty__title">Kéo thả file vào đây</div>
                  <div class="empty__description">
                    Tối đa <?= $max_mb ?>MB · Ảnh, Video, PDF, Word
                  </div>
                </div>
                <div class="empty__content">
                  <span>hoặc</span>
                  <button type="button" class="btn" data-variant="primary" data-size="md" data-mu-trigger>
                    Chọn file mới
                  </button>
                </div>
              </div>

              <div class="media-preview-content" hidden data-mu-preview>
                <div class="media-preview-frame" data-mu-preview-frame></div>
                <p class="text-sm" data-mu-preview-info></p>
              </div>

            </div>

            <div class="field">
              <label class="field__label" for="title">Tiêu đề</label>
              <input id="title" class="field__input" type="text"
                     name="title"
                     value="<?= htmlspecialchars($old_input['title'] ?? $media->title) ?>">
              <p class="field__description">Đặt tên dễ nhớ (Optional)</p>
            </div>

            <div class="field">
              <label class="field__label" for="alt_text">Alt text</label>
              <input id="alt_text" class="field__input" type="text"
                     name="alt_text" placeholder="Mô tả nội dung media (SEO, accessibility)"
                     value="<?= htmlspecialchars($old_input['alt_text'] ?? $media->alt_text) ?>">
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
  const zone       = document.querySelector('[data-mu-zone]');
  const titleInput = document.querySelector('#title');
  const form       = document.querySelector('#media-form');
  const confirmBtn = document.querySelector('#confirm-btn');

  zone.addEventListener('mu:file-selected', (e) => {
    const { name } = e.detail;
    if (!titleInput.value.trim()) {
      titleInput.value = name.replace(/\.[^.]+$/, '');
    }
  });

  zone.addEventListener('mu:error', (e) => {
    const { reason, maxBytes } = e.detail;
    if (reason === 'type') alert('Định dạng file không được hỗ trợ.');
    if (reason === 'size') alert(`File vượt quá dung lượng cho phép (${Math.round(maxBytes / 1024 / 1024)} MB).`);
  });

  confirmBtn.addEventListener('click', () => form.submit());
});
</script>