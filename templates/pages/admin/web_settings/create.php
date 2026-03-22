<?php
$errors = request()->getErrors() ?? [];
$old_input = request()->getOldInputs() ?? [];
$oldType = $old_input['type'] ?? '';
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<div class="detail-panel card shadow">
  <div class="card__header">
    <div class="card__title">
      <h6>Tạo setting mới</h6>
    </div>
    <div class="card__description">
      Thêm một cài đặt mới vào hệ thống. Các setting hệ thống thường được seed trực tiếp qua SQL.
    </div>
  </div>

  <div class="card__content">
    <?php include BASE_PATH . '/templates/components/flash_alert.php'; ?>

    <form id="settings-create-form" method="POST" action="<?= url('admin/web_settings') ?>">
      <div class="field-group">

        <div class="field" data-field-required>
          <label for="key">Key</label>
          <input id="key" class="field__input" type="text" name="key"
            value="<?= htmlspecialchars($old_input['key'] ?? '') ?>" placeholder="VD: homepage.hero_title">
        </div>

        <div class="field" data-field-required>
          <label for="group">Nhóm</label>
          <input id="group" class="field__input" type="text" name="group" list="group-suggestions"
            value="<?= htmlspecialchars($old_input['group'] ?? '') ?>" placeholder="VD: general, homepage, seo"
            autocomplete="off">
          <datalist id="group-suggestions">
            <?php foreach ($groups as $g): ?>
              <option value="<?= htmlspecialchars($g) ?>">
              <?php endforeach; ?>
          </datalist>
        </div>

        <div class="field" data-field-required>
          <label for="type">Kiểu dữ liệu</label>
          <select id="type" class="field__input" name="type">
            <option value="">-- Chọn kiểu --</option>
            <?php foreach ($allowedTypes as $type): ?>
              <option value="<?= $type ?>" <?= $oldType === $type ? 'selected' : '' ?>>
                <?= $type ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field" data-field-required>
          <label for="label">Label</label>
          <input id="label" class="field__input" type="text" name="label"
            value="<?= htmlspecialchars($old_input['label'] ?? '') ?>" placeholder="VD: Tiêu đề trang chủ">
        </div>

        <div class="field">
          <label for="description">Mô tả</label>
          <input id="description" class="field__input" type="text" name="description"
            value="<?= htmlspecialchars($old_input['description'] ?? '') ?>"
            placeholder="Gợi ý hiển thị bên dưới input trong admin">
        </div>

        <!-- field value — nội dung được swap bởi JS khi type thay đổi -->
        <div class="field" id="value-field">
          <label for="value">Giá trị</label>
          <input id="value" class="field__input" type="text" name="value"
            value="<?= htmlspecialchars($old_input['value'] ?? '') ?>" placeholder="Để trống nếu chưa có giá trị">
        </div>

        <div class="field">
          <label for="default_value">Giá trị mặc định</label>
          <input id="default_value" class="field__input" type="text" name="default_value"
            value="<?= htmlspecialchars($old_input['default_value'] ?? '') ?>" placeholder="Fallback khi value bị NULL">
        </div>

        <div class="field">
          <label for="sort_order">Thứ tự hiển thị</label>
          <input id="sort_order" class="field__input" type="number" name="sort_order" min="0"
            value="<?= htmlspecialchars($old_input['sort_order'] ?? '0') ?>">
        </div>

        <div class="field">
          <label class="field__toggle">
            <input type="checkbox" name="autoload" value="1" <?= !empty($old_input['autoload']) ? 'checked' : '' ?>>
            <span class="field__toggle-track"></span>
            <span class="field__toggle-label">Autoload — load vào cache mỗi request</span>
          </label>
        </div>

      </div>
    </form>
  </div>

  <div class="card__footer">
    <button data-modal-trigger="#confirm-modal" type="button" data-variant="primary" data-size="lg" class="btn">
      Thêm
    </button>
  </div>
</div>

<!-- Confirm create modal -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận tạo setting</h2>
    <p class="modal__description">Bạn có chắc muốn tạo setting mới này?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Xác nhận</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
      <path
        d="M55.1 73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L147.2 256 9.9 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192.5 301.3 329.9 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.8 256 375.1 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192.5 210.7 55.1 73.4z" />
    </svg>
  </button>
</div>

<div class="modal-overlay" data-modal-close></div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#settings-create-form');
    const typeSelect = document.querySelector('#type');
    const valueField = document.querySelector('#value-field');
    const confirmBtn = document.querySelector('#confirm-modal-btn');

    new Modal('#confirm-modal');
    confirmBtn.addEventListener('click', () => form.submit());

    // ── Value field renderer ─────────────────────────────────────────────────
    // Ánh xạ type → cách render input cho field "Giá trị"
    const VALUE_PLACEHOLDERS = {
      email: 'VD: admin@university.edu.vn',
      url: 'VD: https://example.com hoặc /gioi-thieu',
      datetime: '',
      int: 'VD: 10',
      float: 'VD: 3.14',
      default: 'Để trống nếu chưa có giá trị',
    };


    /**
     * Render input phù hợp vào #value-field dựa trên type được chọn.
     * Giữ lại giá trị cũ khi switch giữa các text-based type.
     * Reset về rỗng khi switch sang bool.
     */
    function renderValueInput(type) {
      // Lưu giá trị hiện tại trước khi replace
      const current = valueField.querySelector('[name="value"]');
      const oldVal = (current && current.tagName !== 'INPUT' || current?.type === 'file')
        ? ''
        : (current?.value ?? '');

      // Xóa toàn bộ nội dung cũ (trừ label)
      const label = valueField.querySelector('label');
      valueField.innerHTML = '';
      valueField.appendChild(label);


      let input;

      switch (type) {
        case 'text':
          input = document.createElement('textarea');
          input.name = 'value';
          input.className = 'field__input';
          input.rows = 3;
          input.value = oldVal;
          input.placeholder = VALUE_PLACEHOLDERS.default;
          valueField.appendChild(input);
          break;

        case 'json':
          input = document.createElement('textarea');
          input.name = 'value';
          input.className = 'field__input field__input--json';
          input.value = oldVal;
          input.placeholder = '[{"label":"Sinh viên","value":"2500+"}]';
          valueField.appendChild(input);
          break;

        case 'bool': {
          // Hidden + toggle — không cần "value" text field
          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = 'value';
          hidden.value = '0';

          const toggleLabel = document.createElement('label');
          toggleLabel.className = 'field__toggle';

          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.name = 'value';
          checkbox.value = '1';

          // Sync hidden khi toggle
          checkbox.addEventListener('change', () => {
            labelSpan.textContent = checkbox.checked ? 'Bật' : 'Tắt';
          });

          const track = document.createElement('span');
          track.className = 'field__toggle-track';

          const labelSpan = document.createElement('span');
          labelSpan.className = 'field__toggle-label';
          labelSpan.textContent = 'Tắt';

          toggleLabel.append(hidden, checkbox, track, labelSpan);
          valueField.appendChild(toggleLabel);
          break;
        }

        case 'int':
        case 'float': {
          input = document.createElement('input');
          input.type = 'number';
          input.step = type === 'float' ? 'any' : '1';
          input.name = 'value';
          input.className = 'field__input';
          input.value = oldVal;
          input.placeholder = VALUE_PLACEHOLDERS[type] ?? '';
          valueField.appendChild(input);
          break;
        }

        case 'email':
          input = document.createElement('input');
          input.type = 'email';
          input.name = 'value';
          input.className = 'field__input';
          input.value = oldVal;
          input.placeholder = VALUE_PLACEHOLDERS.email;
          valueField.appendChild(input);
          break;

        case 'url':
          input = document.createElement('input');
          input.type = 'url';
          input.name = 'value';
          input.className = 'field__input';
          input.value = oldVal;
          input.placeholder = VALUE_PLACEHOLDERS.url;
          valueField.appendChild(input);
          break;

        case 'datetime':
          input = document.createElement('input');
          input.type = 'datetime-local';
          input.name = 'value';
          input.className = 'field__input';
          input.value = oldVal;
          valueField.appendChild(input);
          break;

        case 'color': {
          const wrapper = document.createElement('div');
          wrapper.className = 'flex items-center gap-2';

          input = document.createElement('input');
          input.type = 'color';
          input.name = 'value';
          input.className = 'field__input';
          input.value = oldVal || '#000000';

          const hex = document.createElement('span');
          hex.className = 'text-sm';
          hex.textContent = input.value;
          input.addEventListener('input', () => { hex.textContent = input.value; });

          wrapper.append(input, hex);
          valueField.appendChild(wrapper);
          break;
        }

        case 'image':
        case 'file': {
          const accept = type === 'image' ? 'image/*' : '*/*';
          input = document.createElement('input');
          input.type = 'file';
          input.name = 'value_file';
          input.accept = accept;
          input.className = 'field__input';
          valueField.appendChild(input);
          break;
        }

        default: // string
          input = document.createElement('input');
          input.type = 'text';
          input.name = 'value';
          input.className = 'field__input';
          input.value = oldVal;
          input.placeholder = VALUE_PLACEHOLDERS.default;
          valueField.appendChild(input);
          break;
      }
    }

    // Kích hoạt ngay khi load (cho trường hợp old_input có type)
    if (typeSelect.value) {
      renderValueInput(typeSelect.value);
    }

    typeSelect.addEventListener('change', () => {
      const type = typeSelect.value;
      renderValueInput(type);
      // enctype phải là multipart/form-data khi có file upload
      form.enctype = ['image', 'file'].includes(type)
        ? 'multipart/form-data'
        : 'application/x-www-form-urlencoded';
    });
  });
</script>