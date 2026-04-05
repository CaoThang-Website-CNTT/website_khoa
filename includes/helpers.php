<?php

use App\Core\Request;

if (!defined('APP_URL')) {
  $projectDir = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', __DIR__ . '/../'));
  $baseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . rtrim($projectDir, '/') . '/';
  define('APP_URL', $baseUrl);
}

/**
 * Hàm hỗ trợ tạo link tuyệt đối
 * @param string $path Đường dẫn mong muốn (ví dụ: 'assets/css/main.css')
 */
function url(string $path = ''): string
{
  return APP_URL . ltrim($path, '/');
}

/**
 * Token CSRF hiện tại (tạo/lấy từ session). Dùng cho meta tag hoặc header AJAX.
 */
function csrf_token(): string
{
  return request()->session()->csrfToken();
}

/**
 * Input ẩn `_token` cho form POST (giống @csrf của Laravel).
 */
function csrf_field(): string
{
  $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
  return '<input type="hidden" name="_token" value="' . $t . '">';
}

function request(): Request
{
  static $instance = null;

  if ($instance === null) {
    $instance = Request::capture();
  }

  return $instance;
}

/**
 * Tạo slug URL-friendly từ chuỗi tiếng Việt hoặc tiếng Anh.
 * Chuyển ký tự có dấu sang không dấu, thay ký tự đặc biệt bằng dấu gạch ngang.
 *
 * @param string $str Chuỗi đầu vào (VD: tên danh mục, tiêu đề bài viết)
 * @return string Slug đã được chuẩn hóa (VD: "tin-tuc-cong-nghe")
 */
function generateSlug(string $str): string
{
  $slug = mb_strtolower($str, 'UTF-8');

  // Chuyển đổi ký tự tiếng Việt sang không dấu
  $map = [
    'à' => 'a',
    'á' => 'a',
    'ả' => 'a',
    'ã' => 'a',
    'ạ' => 'a',
    'ă' => 'a',
    'ắ' => 'a',
    'ặ' => 'a',
    'ằ' => 'a',
    'ẵ' => 'a',
    'ẳ' => 'a',
    'â' => 'a',
    'ấ' => 'a',
    'ầ' => 'a',
    'ẩ' => 'a',
    'ẫ' => 'a',
    'ậ' => 'a',
    'đ' => 'd',
    'è' => 'e',
    'é' => 'e',
    'ẻ' => 'e',
    'ẽ' => 'e',
    'ẹ' => 'e',
    'ê' => 'e',
    'ế' => 'e',
    'ề' => 'e',
    'ể' => 'e',
    'ễ' => 'e',
    'ệ' => 'e',
    'ì' => 'i',
    'í' => 'i',
    'ỉ' => 'i',
    'ĩ' => 'i',
    'ị' => 'i',
    'ò' => 'o',
    'ó' => 'o',
    'ỏ' => 'o',
    'õ' => 'o',
    'ọ' => 'o',
    'ô' => 'o',
    'ố' => 'o',
    'ồ' => 'o',
    'ổ' => 'o',
    'ỗ' => 'o',
    'ộ' => 'o',
    'ơ' => 'o',
    'ớ' => 'o',
    'ờ' => 'o',
    'ở' => 'o',
    'ỡ' => 'o',
    'ợ' => 'o',
    'ù' => 'u',
    'ú' => 'u',
    'ủ' => 'u',
    'ũ' => 'u',
    'ụ' => 'u',
    'ư' => 'u',
    'ứ' => 'u',
    'ừ' => 'u',
    'ử' => 'u',
    'ữ' => 'u',
    'ự' => 'u',
    'ỳ' => 'y',
    'ý' => 'y',
    'ỷ' => 'y',
    'ỹ' => 'y',
    'ỵ' => 'y',
  ];
  $slug = strtr($slug, $map);

  // Ký tự không phải chữ-số → dấu gạch ngang
  $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
  return trim($slug, '-');
}