<?php

use App\Core\Request;

if (!defined('APP_URL')) {
  if (isset($_ENV['APP_URL']) && !empty($_ENV['APP_URL'])) {
    $baseUrl = rtrim($_ENV['APP_URL'], '/') . '/';
  } else {
    $projectDir = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', __DIR__ . '/../'));
    $baseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . rtrim($projectDir, '/') . '/';
  }

  define('APP_URL', $baseUrl);
}

/**
 * Hàm hỗ trợ tạo link tuyệt đối
 * @param string $path Đường dẫn mong muốn (ví dụ: 'assets/css/main.css' hoặc 'https://google.com')
 * @param bool $strict Nếu true (chặt chẽ), link ngoại sẽ bị ép về domain dự án. Nếu false (nới lỏng), giữ nguyên link ngoại.
 */
function url(string $path = '', bool $strict = false): string
{
  if ($path === '') {
    return APP_URL;
  }

  if ($path === 'public/media' || $path === 'public/media/') {
    $path = 'public/img/default-post-thumb.jpg';
  }

  if (preg_match('/^https?:\/\//i', $path)) {
    $appHost = parse_url(APP_URL, PHP_URL_HOST);
    $pathHost = parse_url($path, PHP_URL_HOST);

    if ($pathHost !== $appHost) {
      if ($strict) {
        $parsed = parse_url($path);
        $relative = ($parsed['path'] ?? '') .
          (isset($parsed['query']) ? '?' . $parsed['query'] : '') .
          (isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '');
        return APP_URL . ltrim($relative, '/');
      }
      return $path;
    }
    return $path;
  }
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
  return Request::capture();
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

/**
 * Định dạng SEO title: {pageTitle} | {siteTitle}
 */
function seo_title(string $pageTitle, ?string $siteTitle = null): string
{
  $pageTitle = trim($pageTitle);
  $siteTitle = trim($siteTitle ?? APP_URL);

  if ($pageTitle === '') {
    return $siteTitle;
  }
  if ($siteTitle === '') {
    return $pageTitle;
  }
  return $pageTitle . ' | ' . $siteTitle;
}

/**
 * Generate canonical URL based on current request or provided path
 */
function seo_canonical(?string $path = null): string
{
  if ($path !== null) {
    return url($path);
  }

  $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
  // Xóa query string
  $pathOnly = parse_url($requestUri, PHP_URL_PATH) ?? '';

  // Xóa thư mục con nếu có
  $appPath = parse_url(APP_URL, PHP_URL_PATH) ?? '';
  $appPath = trim($appPath, '/');
  if ($appPath !== '') {
    $pathOnly = preg_replace('/^\/' . preg_quote($appPath, '/') . '(\/|$)/i', '/', $pathOnly);
  }

  return url(ltrim($pathOnly, '/'));
}

/**
 * Render Open Graph meta tags
 */
function seo_og_tags(array $data): string
{
  $html = [];
  foreach ($data as $property => $content) {
    if (!empty($content)) {
      $html[] = sprintf(
        '<meta property="%s" content="%s">',
        htmlspecialchars($property, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
      );
    }
  }
  return implode("\n  ", $html);
}

/**
 * Render Twitter Card meta tags
 */
function seo_twitter_tags(array $data): string
{
  $html = [];
  foreach ($data as $name => $content) {
    if (!empty($content)) {
      $html[] = sprintf(
        '<meta name="%s" content="%s">',
        htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
      );
    }
  }
  return implode("\n  ", $html);
}

/**
 * Render JSON-LD script tag
 */
function seo_jsonld(array $data): string
{
  if (empty($data)) return '';

  $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  return "<script type=\"application/ld+json\">\n" . $json . "\n</script>";
}

/**
 * Render all SEO head tags from one normalized payload.
 */
function seo_head(array $data): string
{
  $siteTitle = $data['siteTitle'] ?? APP_URL;
  $title = $data['title'] ?? $siteTitle;
  if (!empty($data['title']) && !str_contains($title, ' | ')) {
    $title = seo_title($title, $siteTitle);
  }

  $html = [
    '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>',
  ];

  if (!empty($data['description'])) {
    $html[] = '<meta name="description" content="' . htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8') . '">';
  }

  $html[] = '<link rel="canonical" href="' . htmlspecialchars(seo_canonical($data['canonical'] ?? null), ENT_QUOTES, 'UTF-8') . '">';

  if (!empty($data['meta']) && is_array($data['meta'])) {
    $ogTags = [];
    $twitterTags = [];

    foreach ($data['meta'] as $name => $content) {
      if (str_starts_with((string) $name, 'twitter:')) {
        $twitterTags[$name] = $content;
      } else {
        $ogTags[$name] = $content;
      }
    }

    if (!empty($ogTags)) {
      $html[] = seo_og_tags($ogTags);
    }
    if (!empty($twitterTags)) {
      $html[] = seo_twitter_tags($twitterTags);
    }
  }

  $schemas = $data['jsonld'] ?? [];
  if (!empty($schemas)) {
    if (array_is_list($schemas)) {
      foreach ($schemas as $schema) {
        if (is_array($schema)) {
          $html[] = seo_jsonld($schema);
        }
      }
    } elseif (is_array($schemas)) {
      $html[] = seo_jsonld($schemas);
    }
  }

  return implode("\n", array_filter($html));
}
