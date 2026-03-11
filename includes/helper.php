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
