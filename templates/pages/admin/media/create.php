<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
$max_mb = (int) ($_ENV['MAX_UPLOAD_SIZE'] ?? 5);
?>


<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Thêm mới media</h2>
<p class="title-wrapper__description">Upload & điền thông tin media mới vào các trường dưới đây</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/media') ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i> Quay lại
</a>
<button data-modal-trigger="#confirm-modal" type="button" data-variant="primary" data-size="lg" class="btn">
  Lưu
</button>
<?php $layout->end() ?>

<div class="detail-layout">
  <div class="detail-layout__main flex-1">
    <div class="card shadow">

      <div class="card__header">
        <legend class="card__title field__legend">Thêm media</legend>
        <p class="card__description field__description">Upload file ảnh, video hoặc tài liệu từ máy tính</p>
      </div>

      <hr class="separator">

      <div class="card__content">
        <form id="media-form" method="POST" action="<?= url('admin/media') ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="field-group">

            <div class="media-upload-zone" role="button" tabindex="0" aria-label="Khu vực kéo thả file" data-mu-zone
              data-mu-max-bytes="<?= $max_mb * 1024 * 1024 ?>">

              <input type="file" name="file" accept="image/*,video/*,application/pdf,.doc,.docx,.txt" required hidden
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
                    Chọn file
                  </button>
                </div>
              </div>

              <div class="media-preview-content" hidden data-mu-preview>
                <div class="media-preview-frame" data-mu-preview-frame></div>
                <p class="text-sm" data-mu-preview-info></p>
                <button type="button" class="btn" data-variant="destructive" data-size="sm" data-mu-remove>
                  <i class="fa-solid fa-trash"></i> Xóa
                </button>
              </div>

            </div>

            <div class="field">
              <label class="field__label" for="title">Tiêu đề</label>
              <input id="title" class="field__input" type="text" name="title"
                value="<?= htmlspecialchars($old_input['title'] ?? '') ?>">
              <p class="field__description">Đặt tên dễ nhớ (Optional)</p>
            </div>

            <div class="field">
              <label class="field__label" for="alt_text">Alt text</label>
              <input id="alt_text" class="field__input" type="text" name="alt_text"
                placeholder="Mô tả nội dung media (SEO, accessibility)"
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
    <h3 class="modal__title">Xác nhận lưu</h3>
    <p class="modal__description">Bạn có chắc muốn thêm media này?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-btn" data-variant="primary" data-size="lg" class="btn" type="button">Lưu</button>
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
<script src="<?= url('public/js/pages/admin/media/create.js') ?>" type="module"></script>
<?php $layout->end() ?>
