<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trang không tồn tại (404)</title>
  <style>
    body {
      font-family: system-ui, sans-serif;
      max-width: 32rem;
      margin: 4rem auto;
      padding: 0 1rem;
      line-height: 1.5;
    }
  </style>
</head>

<body>
  <h1>404 - Trang không tồn tại</h1>
  <p>Trang không tồn tại. Vui lòng tải lại trang và thử lại.</p>
  <p><a href="javascript:history.back()">Quay lại</a> · <a
      href="<?= htmlspecialchars(url(''), ENT_QUOTES, 'UTF-8') ?>">Trang chủ</a></p>
</body>

</html>