<?php
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
 * Hàm hỗ trợ lưu flash message nhằm để tạo thông báo flash tại UI
 * @param string $type
 * @param string $title
 * @param string $desc
 * @return void
 */
function flash(string $type, string $title, string $desc = ''): void
{
  $_SESSION['_flash'] = compact('type', 'title', 'desc');
}