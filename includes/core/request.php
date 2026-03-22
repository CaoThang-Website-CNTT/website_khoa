<?php

namespace App\Core;

class Request
{
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
    return new static(
      $_GET,
      $_POST,
      $_FILES,
      $_SERVER,
    );
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
      $parsed = json_decode($raw, true) ?? [];
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

  public function flash(string $type, string $title, string $desc = ""): void
  {
    $_SESSION['flash'] = compact('type', 'title', 'desc');
  }
  public function getFlash()
  {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
  }

  public function flashOldInputs(?array $includedKeys = null, ?array $excludedKeys = null): void
  {
    if ($includedKeys !== null) {
      $_SESSION['old_input'] = array_intersect_key(
        $this->body,
        array_flip($includedKeys)
      );
    } elseif ($excludedKeys !== null) {
      $_SESSION['old_input'] = array_diff_key(
        $this->body,
        array_flip($excludedKeys)
      );
    } else {
      $_SESSION['old_input'] = $this->body;
    }
  }

  public function getOldInputs(): array
  {
    $olds = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);
    return $olds;
  }

  public function flashErrors(array $errors): void
  {
    $_SESSION['errors'] = $errors;
  }

  public function getErrors(): array
  {
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);
    return $errors;
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