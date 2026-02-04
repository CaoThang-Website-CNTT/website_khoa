<?php

require_once('../models/user_model.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $username = isset($_POST['username']) ? trim($_POST['username']) : '';
  $password = isset($_POST['password']) ? $_POST['password'] : '';

  // Kiểm tra rỗng
  if (empty($username) || empty($password)) {
    echo "Lỗi: Vui lòng nhập đầy đủ Mã SV và Mật khẩu.";
    exit();
  }

  // Kiểm tra mã sv có phải là dãy số không
  if (!ctype_digit($username)) {
    echo "Lỗi: Mã sinh viên không hợp lệ (Chỉ được chứa các chữ số).";
    exit();
  }

  $userModel = new UserModel();
  $user = $userModel->checkCredentials($username, $password);

  if ($user) {
    // TODO: lưu session

    // Redirect về trang chủ
    header('Location: ../index.php');
    exit();
  } else {
    echo "Lỗi: Sai Mã sinh viên hoặc Mật khẩu.";
  }
} else {
  // Chặn việc truy cập file này trực tiếp mà không submit form
  header('Location: ../login.php');
}
