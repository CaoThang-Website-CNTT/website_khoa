<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" sizes="32x32" href="./public/favicon-32x32.png">
  <link rel="preload" as="style" href="./public/css/fonts.css">
  <link rel="stylesheet" href="./public/css/fonts.css">
  <link rel="preload" as="style" href="./public/css/base.css">
  <link rel="stylesheet" href="./public/css/base.css">
  <link rel="preload" as="style" href="./public/css/common.css">
  <link rel="stylesheet" href="./public/css/common.css">
  <link rel="preload" as="style" href="./public/css/main.css">
  <link rel="stylesheet" href="./public/css/main.css">
  <link rel="stylesheet" href="./public/css/auth.css">
  <link rel="stylesheet" href="./public/css/carousel.css">
  <title>Khoa Công nghệ Thông tin - Trường CĐKT Cao Thắng</title>
  <script src="./public/js/utils.js"></script>
</head>

<body>
  <!-- HEADER: START -->
  <?php require 'includes/header.php'; ?>
  <!-- HEADER: END -->

  <main class="container flex justify-center items-center py-16">
    <div class="login-card rounded-2xl shadow-sm p-8 flex flex-col items-center gap-6 w-full max-w-md">

      <!-- Title -->
      <h1 class="login-title text-2xl font-bold">Đăng Nhập</h1>

      <!-- Form -->
      <form action="./controllers/auth_controller.php" method="POST" class="w-full flex flex-col gap-4">

        <!-- Field 1 -->
        <div class="flex flex-col gap-2 items-start w-full">
          <label for="username" class="login-field__label text-sm">Mã SV</label>
          <input type="text" id="username" name="username" placeholder="MSSV" value="0306231001"
            class="login-field__input w-full h-12 rounded-md px-3 py-2 text-sm">
        </div>

        <!-- Field 2 -->
        <div class="flex flex-col gap-2 items-start w-full">
          <label for="password" class="login-field__label text-sm">Mật Khẩu</label>
          <input type="password" id="password" name="password" placeholder="Mật khẩu" value="123456"
            class="login-field__input w-full h-12 rounded-md px-3 py-2 text-sm">
        </div>

        <!-- Submit Button -->
        <button type="submit" class="primary-btn w-full rounded-md py-3 font-semibold text-base mt-2 bouncy-btn">
          Đăng Nhập
        </button>

      </form>

      <!-- Footer/Link -->
      <div class="login-footer__text text-sm flex gap-1">
        <span>Quên Mật Khẩu? Liên hệ</span>
        <a href="#" class="login-footer__link underline">P.CTCT-HSSV</a>
      </div>

    </div>
  </main>

  <!-- FOOTER: START -->
  <?php require 'includes/footer.php'; ?>
  <!-- HEADER: END -->
</body>

</html>