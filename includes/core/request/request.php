<?php

namespace App\Core;

class Request
{
  protected static $instance = null;
  /**
   * Thông tin của một session
   * @var Session
   */
  protected Session $session;
  /**
   * Request headers (lấy từ $_SERVER)
   * @var array
   */
  protected array $headers;

  /**
   * POST body parameters ($_POST)
   * @var array
   */
  protected array $body;

  /**
   * Query-string parameters ($_GET)
   * @var array
   */
  protected array $query;

  /**
   * File được upload ($_FILES)
   * @var array
   */
  protected array $files;

  /**
   * Lưu thông tin Server và Host Env (Chủ yếu để lấy Request Method)
   * @var array
   */
  protected array $server;

  public function __construct(
    array $query = [],
    array $body = [],
    array $files = [],
    array $server = [],
  ) {
    $this->query = $query;
    $this->body = $body;
    $this->files = $this->normaliseFiles($files);
    $this->server = $server;
    $this->headers = $this->parseHeaders($server);
  }

  /**
   * Tạo một Request từ các biến cục bộ của PHP (Snapshot tại thời điểm lấy)
   * @return static
   */
  public static function capture(): static
  {
    if (self::$instance === null) {
      $body = $_POST;
      $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

      // Nếu Request là JSON, hợp nhất nó vào $body chung
      if (str_contains(strtolower($contentType), '/json')) {
        $jsonPayload = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonPayload)) {
          $body = array_merge($body, $jsonPayload);
        }
      }

      self::$instance = new static(
        $_GET,
        $body,
        $_FILES,
        $_SERVER,
      );
    }

    return self::$instance;
  }

  // ===================================
  // HTTP Method
  // ===================================

  /**
   * Lấy HTTP method của request hiện tại.
   * Hỗ trợ method spoofing qua header X-HTTP-Method-Override hoặc input ẩn _method
   * @return string VD: 'GET', 'POST', 'PUT', 'PATCH', 'DELETE'
   */
  public function method(): string
  {
    $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'POST') {
      // Do HTML form chỉ hỗ trợ GET/POST nên ta dùng Method Spoofing
      // bằng cách tạo input:hidden với name="_method" hoặc set trên Header
      // để có thể lưu khi các method như PUT/PATCH/DELETE

      // Kiểm tra trên Header (AJAX) và input thường
      $override = $this->header('X-HTTP-Method-Override') ?? ($this->body['_method'] ?? null);

      if ($override) {
        $method = strtoupper($override);
      }
    }

    return $method;
  }

  /**
   * Kiểm tra HTTP method của request có khớp với method cho trước không
   * @param string $method HTTP method cần kiểm tra. VD: 'GET', 'POST'
   * @return bool
   */
  public function isMethod(string $method): bool
  {
    return $this->method() === strtoupper($method);
  }

  /**
   * Lấy URI của request, không bao gồm query string
   * @return string VD: '/students/42'
   */
  public function uri(): string
  {
    return strtok($this->server['REQUEST_URI'] ?? '/', '?') ?: '/';
  }

  /**
   * Lấy URI đầy đủ của request, bao gồm cả query string
   * @return string VD: '/students/42?tab=grades'
   */
  public function fullUri(): string
  {
    return $this->server['REQUEST_URI'] ?? '/';
  }

  /**
   * Lấy path của request, đã strip base path prefix.
   * Dùng để so sánh với URL trong menu mà không bị ảnh hưởng bởi subfolder.
   * VD: REQUEST_URI=/website_khoa/gioi-thieu → path()=/gioi-thieu
   * @return string
   */
  public function path(): string
  {
    $uri = $this->uri();
    $basePath = rtrim(dirname($this->server['SCRIPT_NAME'] ?? ''), '/');
    $path = $basePath ? str_replace($basePath, '', $uri) : $uri;
    return '/' . ltrim($path, '/') ?: '/';
  }

  /**
   * Lấy URL trước đó (Referer), có xử lý chống vòng lặp (Refresh Trap) và Open Redirect.
   * @param string $fallback Đường dẫn dự phòng nếu không có referer hợp lệ
   * @return string
   */
  public function previous(string $fallback = 'index'): string
  {
    $protocol = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $this->server['HTTP_HOST'] ?? 'localhost';
    $uri = $this->server['REQUEST_URI'] ?? '/';
    $currentUrl = $protocol . '://' . $host . $uri;

    // Thử lấy previous URL thực tế từ Session (đã lọc các đợt submit POST/redirect)
    if (isset($this->session)) {
      $previousUrl = $this->session->get('_url.previous');
      if ($previousUrl && $previousUrl !== $currentUrl) {
        return htmlspecialchars($previousUrl, ENT_QUOTES, 'UTF-8');
      }
    }

    // Dự phòng (Fallback) về HTTP_REFERER nếu không có lịch sử Session
    $referer = $this->server['HTTP_REFERER'] ?? null;

    if (!$referer) {
      return $fallback;
    }

    $refererHost = parse_url($referer, PHP_URL_HOST);

    if ($refererHost !== $host) {
      return $fallback;
    }

    if ($referer === $currentUrl) {
      return $fallback;
    }

    return htmlspecialchars($referer, ENT_QUOTES, 'UTF-8');
  }

  // ===================================
  // Header
  // ===================================

  /**
   * Parse các HTTP header từ mảng $_SERVER thành định dạng chuẩn hóa
   * @param array $server Mảng $_SERVER
   * @return array Mảng header với key đã được chuẩn hóa về chữ thường. VD: ['content-type' => 'application/json']
   */
  protected function parseHeaders(array $server): array
  {
    $headers = [];

    foreach ($server as $key => $value) {
      if (str_starts_with($key, 'HTTP_')) {
        $name = strtolower(str_replace('_', '-', substr($key, 5)));
        $headers[$name] = $value;
      } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
        $name = strtolower(str_replace('_', '-', $key));
        $headers[$name] = $value;
      }
    }

    return $headers;
  }

  /**
   * Lấy một hoặc tất cả HTTP header của request
   * @param string|null $key Tên header cần lấy (không phân biệt hoa thường). Null để lấy tất cả
   * @param mixed $default Giá trị mặc định nếu header không tồn tại
   * @return mixed Giá trị header hoặc toàn bộ mảng header nếu $key là null
   */
  public function header(?string $key = null, mixed $default = null): mixed
  {
    if ($key === null) {
      return $this->headers;
    }

    $normalized = strtolower(str_replace('_', '-', $key));

    return $this->headers[$normalized] ?? $default;
  }

  /**
   * Lấy Bearer token từ Authorization header
   * @return string|null Token nếu tồn tại, null nếu không có hoặc không đúng định dạng Bearer
   */
  public function bearerToken(): ?string
  {
    $auth = $this->header('authorization') ?? '';

    if (str_starts_with($auth, 'Bearer ')) {
      return substr($auth, 7);
    }

    return null;
  }

  /**
   * Kiểm tra request có phải là AJAX không dựa vào header X-Requested-With
   * @return bool
   */
  public function isAjax(): bool
  {
    return strtolower($this->header('x-requested-with', '')) === 'xmlhttprequest';
  }

  /**
   * Đọc và parse JSON body từ request (thường dùng với fetch/axios)
   * Kết quả được cache lại để tránh đọc php://input nhiều lần
   * @param string|null $key Tên field cần lấy. Null để lấy toàn bộ
   * @param mixed $default Giá trị mặc định nếu key không tồn tại
   * @return mixed Giá trị field hoặc toàn bộ mảng đã parse nếu $key là null
   */
  public function json(?string $key = null, mixed $default = null): mixed
  {
    static $parsed = null;

    if ($parsed === null) {
      $raw = file_get_contents('php://input');
      $decoded = json_decode($raw, true) ?? [];

      if (json_last_error() !== JSON_ERROR_NONE) {
        $parsed = [];
      } else {
        $parsed = $decoded;
      }
    }

    if ($key === null)
      return $parsed;

    return $parsed[$key] ?? $default;
  }

  /**
   * Lấy một hoặc tất cả query string parameter ($_GET)
   * @param string|null $key Tên parameter cần lấy. Null để lấy tất cả
   * @param mixed $default Giá trị mặc định nếu key không tồn tại
   * @return mixed Giá trị parameter hoặc toàn bộ mảng query nếu $key là null
   */
  public function query(?string $key = null, mixed $default = null): mixed
  {
    if ($key === null)
      return $this->query;
    return $this->query[$key] ?? $default;
  }

  // ===================================
  // Body
  // ===================================

  /**
   * Lấy toàn bộ POST body parameters ($_POST)
   * @return array
   */
  public function all(): array
  {
    return $this->body;
  }

  /**
   * Lấy một giá trị từ POST body theo key
   * @param string $key Tên field cần lấy
   * @param mixed $default Giá trị mặc định nếu key không tồn tại
   * @return mixed
   */
  public function input(string $key, mixed $default = null): mixed
  {
    return $this->body[$key] ?? $default;
  }

  /**
   * Lấy toàn bộ body đã được sanitize (trim chuỗi, đệ quy cho mảng lồng nhau).
   * Dùng thay cho all() khi cần dữ liệu sạch để validate hoặc lưu DB.
   * @return array
   */
  public function sanitized(): array
  {
    return $this->deepTrim($this->body);
  }

  /**
   * Lấy một tập con các field từ body đã sanitize.
   * Dùng để chống mass-assignment — chỉ nhận đúng các field mong muốn.
   *
   * @param array<string> $keys Danh sách key cần lấy. VD: ['title', 'start_at', 'end_at']
   * @return array Mảng chỉ chứa các key được chỉ định, đã trim
   */
  public function only(array $keys): array
  {
    return array_intersect_key($this->sanitized(), array_flip($keys));
  }

  /**
   * Trim đệ quy tất cả giá trị string trong mảng (kể cả mảng lồng nhau).
   * Bỏ qua các giá trị non-string (int, bool, null, v.v.).
   *
   * @param array $data Mảng dữ liệu cần trim
   * @return array Mảng đã trim
   */
  private function deepTrim(array $data): array
  {
    $result = [];
    foreach ($data as $key => $value) {
      if (is_string($value)) {
        $result[$key] = trim($value);
      } elseif (is_array($value)) {
        $result[$key] = $this->deepTrim($value);
      } else {
        $result[$key] = $value;
      }
    }
    return $result;
  }

  public function flashOldInputs(?array $includedKeys = null, ?array $excludedKeys = null): void
  {
    $this->session->flashOldInputs(
      $this->body,
      $includedKeys ?? [],
      $excludedKeys ?? []
    );
  }
  public function setSession(Session $session)
  {
    $this->session = $session;
  }

  public function session(): Session
  {
    return $this->session;
  }

  // ===================================
  // File
  // ===================================

  /**
   * Lấy thông tin một file được upload theo key
   * @param string $key Tên input file
   * @return mixed Mảng thông tin file hoặc null nếu không tồn tại
   */
  public function file(string $key): mixed
  {
    return $this->files[$key] ?? null;
  }

  /**
   * Kiểm tra request có chứa file upload hợp lệ theo key không
   * @param string $key Tên input file
   * @return bool
   */
  public function hasFile(string $key): bool
  {
    $file = $this->file($key);

    if (is_array($file)) {
      return isset($file['tmp_name']) && $file['tmp_name'] !== '';
    }

    return false;
  }

  /**
   * Lấy tất cả file được upload trong request
   * @return array
   */
  public function allFiles(): array
  {
    return $this->files;
  }

  /**
   * Chuẩn hóa các file từ $_FILES thành 1 định dạng thống nhất
   * @param array $files Mảng $_FILES gốc từ PHP
   * @return array Mảng file đã chuẩn hóa, mỗi phần tử có các key: name, type, tmp_name, error, size
   */
  protected function normaliseFiles(array $files): array
  {
    $result = [];

    foreach ($files as $key => $file) {
      // Multiple-file input (<input name="photos[]" multiple>)
      if (is_array($file['name'] ?? null)) {
        $count = count($file['name']);
        $result[$key] = [];

        for ($i = 0; $i < $count; $i++) {
          $result[$key][] = [
            'name' => $file['name'][$i],
            'type' => $file['type'][$i],
            'tmp_name' => $file['tmp_name'][$i],
            'error' => $file['error'][$i],
            'size' => $file['size'][$i],
          ];
        }
      } else {
        $result[$key] = $file;
      }
    }

    return $result;
  }
}
