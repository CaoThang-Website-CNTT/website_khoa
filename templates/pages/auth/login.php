<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>


<div class="card shadow auth-form-card">
  <div class="card__header">
    <div class="card__title">Đăng nhập</div>
    <div class="card__description">
      Hãy sử dụng tài khoản do trường cung cấp để đăng nhập vào các trang web của
      Khoa CNTT
    </div>
  </div>
  <hr class="separator">
  <div class="card__content">
    <form id="login-form" action="<?= url('login') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="field-group">
        <div class="field" data-field-required>
          <label class="field__label" for="email">Email</label>
          <input id="email" class="field__input" type="email" name="email" placeholder="VD: example@university.edu"
            value="<?= htmlspecialchars($old_input['email'] ?? '') ?>" autocomplete="username">
        </div>

        <div class="field" data-field-required>
          <label class="field__label" for="password">Mật khẩu</label>
          <div class="password-field">
            <input id="password" class="field__input" type="password" name="password" placeholder="Nhập mật khẩu"
              autocomplete="current-password">
            <button class="password-field__toggle" type="button" data-password-toggle="password"
              aria-label="Show password">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
  <div class="card__footer">
    <button id="login-submit-form-btn" type="submit" form="login-form" class="btn" data-variant="primary" data-size="lg">
      Đăng nhập
    </button>
    <a href="<?= $authLoginUrl ?>" class="btn" data-variant="outline" data-size="lg">
      <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"
        xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block;">
        <path fill="#EA4335"
          d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z">
        </path>
        <path fill="#4285F4"
          d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z">
        </path>
        <path fill="#FBBC05"
          d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z">
        </path>
        <path fill="#34A853"
          d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z">
        </path>
        <path fill="none" d="M0 0h48v48H0z"></path>
      </svg>
      Đăng nhập bằng Google
    </a>
  </div>
</div>

<?php $layout->start("scripts") ?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>
<script src="<?= url('public/js/pages/auth/login.js') ?>" type="module"></script>
<?php $layout->end() ?>
