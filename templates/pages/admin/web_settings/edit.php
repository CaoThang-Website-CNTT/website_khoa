<?php
$errors = request()->getErrors() ?? [];

/**
 * Render input phù hợp theo type của setting.
 *
 * Naming convention: name="settings[{id}][value]" cho tất cả type.
 * bool: cần hidden input trước checkbox để đảm bảo '0' luôn được gửi khi unchecked.
 */
function renderSettingInput(object $setting): void
{
  $id = $setting->id;
  $name = "settings[{$id}][value]";
  $val = htmlspecialchars($setting->value ?? '');
  $dis = $setting->is_locked ? 'disabled' : '';

  switch ($setting->type) {

    case 'text':
      echo "<textarea name=\"{$name}\" class=\"field__input\" rows=\"3\" {$dis}>{$val}</textarea>";
      break;

    case 'json':
      echo "<textarea name=\"{$name}\" class=\"field__input field__input--json\" {$dis}>{$val}</textarea>";
      break;

    case 'bool':
      $checked = $setting->value === '1' ? 'checked' : '';
      $label = $setting->value === '1' ? 'Đang bật' : 'Đang tắt';
      // Hidden input đảm bảo value '0' luôn được submit khi checkbox không được tick
      echo "<input type=\"hidden\" name=\"{$name}\" value=\"0\" {$dis}>";
      echo "<label class=\"field__toggle\">";
      echo "  <input type=\"checkbox\" name=\"{$name}\" value=\"1\" {$checked} {$dis}>";
      echo "  <span class=\"field__toggle-track\"></span>";
      echo "  <span class=\"field__toggle-label\">{$label}</span>";
      echo "</label>";
      break;

    case 'int':
      echo "<input type=\"number\" step=\"1\" name=\"{$name}\" class=\"field__input\" value=\"{$val}\" {$dis}>";
      break;

    case 'float':
      echo "<input type=\"number\" step=\"any\" name=\"{$name}\" class=\"field__input\" value=\"{$val}\" {$dis}>";
      break;

    case 'color': {
      $colorVal = $setting->value ?: '#000000';
      echo "<input type=\"color\" name=\"{$name}\" class=\"field__input\" value=\"" . htmlspecialchars($colorVal) . "\" {$dis}>";
      break;
    }

    case 'email':
      echo "<input type=\"email\" name=\"{$name}\" class=\"field__input\" value=\"{$val}\" {$dis}>";
      break;

    case 'url':
      echo "<input type=\"url\" name=\"{$name}\" class=\"field__input\" value=\"{$val}\" {$dis}>";
      break;

    case 'datetime':
      echo "<input type=\"datetime-local\" name=\"{$name}\" class=\"field__input\" value=\"{$val}\" {$dis}>";
      break;

    default: // string
      echo "<input type=\"text\" name=\"{$name}\" class=\"field__input\" value=\"{$val}\" {$dis}>";
      break;
  }
}
?>

<script>
  // Batch form không dùng old_input (giá trị DB hiện tại đã được render sẵn).
  // Errors được hiển thị inline qua PHP — không cần FormHandler tự động xử lý.
  window.__errors__ = {};
  window.__old__ = {};
</script>

<?php if ($flash = request()->getFlash()): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= $flash['type'] ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div>
      <h2 class="title text-2xl font-semibold">
        Cài đặt:
        <code><?= htmlspecialchars($group) ?></code>
        <span class="badge" data-variant="secondary">
          <?= count($settings) ?> cài đặt
        </span>
      </h2>
    </div>
    <div class="flex gap-2">
      <a href="<?= url('admin/web_settings/create') ?>" data-variant="primary" data-size="md" class="btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
          <path
            d="M256 64c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 160-160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l160 0 0 160c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160 160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-160 0 0-160z" />
        </svg>
        Thêm setting
      </a>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<!-- ========== Batch form card ========== -->
<div class="card shadow">
  <div class="card__header">
    <div class="card__title">
      <h6>Nhóm: <?= htmlspecialchars($group) ?></h6>
    </div>
    <div class="card__description">
      Chỉnh sửa và lưu toàn bộ cài đặt trong nhóm này cùng lúc.
      Các setting được đánh dấu <strong>Hệ thống</strong> không thể chỉnh sửa hoặc xóa qua UI.
    </div>
  </div>

  <div class="card__content">
    <?php include BASE_PATH . '/templates/components/flash_alert.php'; ?>

    <form id="settings-batch-form" method="POST" action="<?= url('admin/web_settings/' . $group) ?>">

      <div class="field-group">

        <?php foreach ($settings as $setting): ?>

          <div class="field" <?= isset($errors[$setting->id]) ? 'data-field-invalid="true"' : '' ?>>

            <!-- Label + badges + actions -->
            <div class="flex justify-between items-center">

              <label class="flex items-center gap-2">
                <?= htmlspecialchars($setting->label) ?>
                <span class="badge" data-variant="secondary"><?= $setting->type ?></span>
                <?php if ($setting->is_locked): ?>
                  <span class="badge" data-variant="primary" title="Không thể chỉnh sửa qua UI">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor"
                      style="width: .75rem; height: .75rem; display: inline;">
                      <path
                        d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                    </svg>
                    Hệ thống
                  </span>
                <?php endif; ?>
              </label>

              <?php if (!$setting->is_locked): ?>
                <button type="button" data-modal-trigger="#delete-modal-<?= $setting->id ?>" data-variant="destructive"
                  data-size="md" class="btn">
                  Xóa
                </button>
              <?php endif; ?>

            </div>

            <!-- Input theo type -->
            <?php renderSettingInput($setting); ?>

            <!-- Description hint -->
            <?php if ($setting->description): ?>
              <span class="text-sm" style="color: var(--muted-foreground);">
                <?= htmlspecialchars($setting->description) ?>
              </span>
            <?php endif; ?>

            <!-- Inline error — keyed by setting ID từ batchUpdate() -->
            <?php if (isset($errors[$setting->id])): ?>
              <span class="field__error">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                  <path
                    d="M256 512a256 256 0 1 1 0-512 256 256 0 1 1 0 512zm0-192a32 32 0 1 0 0 64 32 32 0 1 0 0-64zm0-192c-18.2 0-32.7 15.5-31.4 33.7l7.4 104c.9 12.6 11.4 22.3 23.9 22.3 12.6 0 23-9.7 23.9-22.3l7.4-104c1.3-18.2-13.1-33.7-31.4-33.7z" />
                </svg>
                <?= htmlspecialchars($errors[$setting->id][0]) ?>
              </span>
            <?php endif; ?>

          </div>

        <?php endforeach; ?>

      </div>
    </form>
  </div>

  <div class="card__footer">
    <button data-modal-trigger="#confirm-save-modal" type="button" data-variant="primary" data-size="lg" class="btn">
      Lưu thay đổi
    </button>
  </div>
</div>

<!-- ========== Confirm save modal ========== -->
<div class="modal" id="confirm-save-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận lưu thay đổi</h2>
    <p class="modal__description">
      Toàn bộ cài đặt trong nhóm <strong><?= htmlspecialchars($group) ?></strong> sẽ được cập nhật.
    </p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-save-btn" data-variant="primary" data-size="lg" class="btn" type="button">Lưu</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
      <path
        d="M55.1 73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L147.2 256 9.9 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192.5 301.3 329.9 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.8 256 375.1 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192.5 210.7 55.1 73.4z" />
    </svg>
  </button>
</div>

<div class="modal-overlay" data-modal-close></div>

<!-- ========== Delete forms + modals — một cặp riêng cho mỗi setting có thể xóa ========== -->
<?php foreach ($settings as $setting): ?>
  <?php if ($setting->is_locked)
    continue; ?>

  <!--
    Form xóa nằm ngoài batch form để tránh lồng form trong HTML.
    POST thẳng đến route /admin/web_settings/delete/{id} — nhất quán với pattern toàn project.
  -->
  <form id="delete-form-<?= $setting->id ?>" method="POST"
    action="<?= url('admin/web_settings/delete/' . $setting->id) ?>">
  </form>

  <div class="modal" id="delete-modal-<?= $setting->id ?>" tabindex="-1" data-state="closed">
    <div class="modal__header">
      <h2 class="modal__title">Xóa cài đặt</h2>
      <p class="modal__description">
        Bạn có chắc muốn xóa <strong><?= htmlspecialchars($setting->label) ?></strong>?
        Thao tác này không thể hoàn tác.
      </p>
    </div>
    <div class="modal__footer">
      <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
      <button id="confirm-delete-<?= $setting->id ?>" data-variant="destructive" data-size="lg" class="btn" type="button">
        Xóa
      </button>
    </div>
    <button class="modal__close" type="button" data-modal-close>
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
        <path
          d="M55.1 73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L147.2 256 9.9 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192.5 301.3 329.9 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.8 256 375.1 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192.5 210.7 55.1 73.4z" />
      </svg>
    </button>
  </div>

  <div class="modal-overlay" data-modal-close></div>

<?php endforeach; ?>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const batchForm = document.querySelector('#settings-batch-form');
    const confirmSaveBtn = document.querySelector('#confirm-save-btn');

    new Modal('#confirm-save-modal');
    confirmSaveBtn.addEventListener('click', () => batchForm.submit());

    // Khởi tạo modal xóa + gắn sự kiện submit form delete cho mỗi setting
    <?php foreach ($settings as $setting): ?>
      <?php if ($setting->is_locked)
        continue; ?>
      new Modal('#delete-modal-<?= $setting->id ?>');
      document.querySelector('#confirm-delete-<?= $setting->id ?>')
        .addEventListener('click', () => {
          document.querySelector('#delete-form-<?= $setting->id ?>').submit();
        });
    <?php endforeach; ?>

  });
</script>