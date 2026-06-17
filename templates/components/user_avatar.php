<?php
$authUser = request()->session()->authUser();
$email = $authUser['email'] ?? 'guest@example.com';
$name = explode('@', $email)[0]; // Tạm thời lấy phần trước @ làm tên
$role = $authUser['role'] ?? 'Guest';
?>

<div class="dropdown avatar-dropdown">
  <button class="dropdown__trigger avatar__trigger" data-dropdown-trigger-mode="click" data-side="right">
    <div class="avatar">
      <div class="avatar__image">
        <img src="<?= url('/public/img/faculty_logo.jpg') ?>" alt="Avatar">
      </div>
      <div class="avatar__info">
        <span class="avatar__name"><?= ucwords(str_replace('.', ' ', $name)) ?></span>
        <span class="avatar__email"><?= $email ?></span>
      </div>
      <i class="fa-solid fa-ellipsis-vertical"></i>
    </div>
  </button>

  <div class="dropdown__content avatar-dropdown__content">
    <a class="dropdown__item avatar-dropdown__item" href="<?= url('') ?>">
      <i class="fa-solid fa-house"></i>
      Về trang chủ
    </a>
    <a class="dropdown__item avatar-dropdown__item--destructive" href="<?= url('logout') ?>">
      <i class="fa-solid fa-right-from-bracket"></i>
      Đăng xuất
    </a>
  </div>
</div>