<?php
$errors = request()->getErrors() ?? [];
$old_input = request()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<div class="detail-panel card shadow">
  <div class="card__header">
    <div class="card__title">
      <h6>Create new Teacher</h6>
    </div>
    <div class="card__description">
      This is creating new Teacher form
    </div>
  </div>
  <div class="card__content">
    <?php
    include BASE_PATH . '/templates/components/flash_alert.php';
    ?>
    <form id="user-add-form" method="POST" action="<?= url('admin/teachers') ?>">
      <div class="field-group">
        <div class="field" data-field-required>
          <label for="email">Email</label>
          <input id="email" class="field__input <?= isset($errors['full_name']) ? 'field__input--error' : '' ?>"
            type="email" name="email" value="">
        </div>

        <div class="field" data-field-required>
          <label for="password">Password</label>
          <input id="password" class="field__input <?= isset($errors['password']) ? 'field__input--error' : '' ?>"
            type="password" name="password" value="">
        </div>

        <div class="field" data-field-required>
          <label for="password_comfirmation">Password Comfirmation</label>
          <input id="password_comfirmation"
            class="field__input <?= isset($errors['password_comfirmation']) ? 'field__input--error' : '' ?>"
            type="password" name="password_comfirmation" value="">
        </div>

        <div class="field" data-field-required>
          <label for="full_name">Full Name</label>
          <input id="full_name" class="field__input <?= isset($errors['full_name']) ? 'field__input--error' : '' ?>"
            type="text" name="full_name" value="">
        </div>

        <div class="field" data-field-required>
          <label for="gender">Gender</label>
          <select id="gender" class="field__input <?= isset($errors['gender']) ? 'field__input--error' : '' ?>"
            name="gender">
            <option value="male">Nam</option>
            <option value="female">Nữ</option>
          </select>
        </div>

        <div class="field" data-field-required>
          <label for="dob">Date of Birth</label>
          <input id="dob" class="field__input <?= isset($errors['dob']) ? 'field__input--error' : '' ?>" type="date"
            name="dob" value="">
        </div>

        <div class="field" data-field-required>
          <label for="phone">Phone</label>
          <input id="phone" class="field__input <?= isset($errors['phone']) ? 'field__input--error' : '' ?>" type="tel"
            name="phone" value="">
        </div>

        <div class="field" data-field-required>
          <label for="start_date">Start Date</label>
          <input id="start_date" class="field__input <?= isset($errors['start_date']) ? 'field__input--error' : '' ?>"
            type="date" name="start_date" value="">
        </div>

        <div class="field">
          <label for="title">Title</label>
          <input id="title" class="field__input <?= isset($errors['title']) ? 'field__input--error' : '' ?>" type="text"
            name="title" value="">
        </div>

        <div class="field">
          <label for="department">Department</label>
          <input id="department" class="field__input <?= isset($errors['department']) ? 'field__input--error' : '' ?>"
            type="text" name="department" value="">
        </div>

    </form>
  </div>
</div>
<div class="card__footer">
  <button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="submit" data-variant="primary" data-size="lg"
    class="w-full btn">Thêm</button>
</div>
</div>

<!-- Add confirm modal -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">
      Những thao tác này sẽ không thể hoàn tác.
    </p>
  </div>

  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Chắc chắn</button>
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
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#user-add-form');
    const createBtn = document.querySelector('#create-submit-btn');
    const confirmBtn = document.querySelector('#confirm-modal-btn');

    const modal = new Modal("#confirm-modal");
    const closeTriggers = document.querySelectorAll('[data-modal-close]');

    let pendingActionUrl = '';

    console.log(modal)

    // Create Btn Event Listener
    createBtn.addEventListener('click', function (e) {
      e.preventDefault();
      pendingActionUrl = form.getAttribute('action');
    });

    // Confirm Btn Event Listener
    confirmBtn.addEventListener('click', function () {
      form.action = pendingActionUrl;
      form.submit();
    });
  });
</script>