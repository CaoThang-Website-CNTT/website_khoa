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

  public function __construct(
    array $query = [],
    array $request = [],
    array $files = [],
    array $server = [],
  ) {
    $this->query = $query;
    $this->request = $request;
    $this->files = $this->normaliseFiles($files);
    $this->headers = $this->parseHeaders($server);
  }

  /**
   * Tạo một Request từ các biến cục bộ của PHP (Snapshot tại thời điểm lấy)
   * @return Request
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

  public function method(): string
  {
    $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'POST') {
      $override = $this->header('X-HTTP-Method-Override') ?? $this->body['_method'];
      if ($override) {
        $method = strtoupper($override);
      }
    }

    return $method;
  }

  public function getMethod(): string
  {
    return $this->method();
  }

  // ===================================
  // Header
  // ===================================
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
  public function header(?string $key = null, mixed $default = null): mixed
  {
    if ($key === null) {
      return $this->headers;
    }

    $normalized = strtolower(str_replace('_', '-', $key));

    return $this->headers[$normalized] ?? $default;
  }

  public function bearerToken(): ?string
  {
    $auth = $this->header('authorization') ?? '';

    if (str_starts_with($auth, 'Bearer ')) {
      return substr($auth, 7);
    }

    return null;
  }

  // ===================================
  // File
  // ===================================
  public function file(string $key): mixed
  {
    return $this->files[$key] ?? null;
  }
  public function hasFile(string $key): bool
  {
    $file = $this->file($key);

    if (is_array($file)) {
      return isset($file['tmp_name']) && $file['tmp_name'] !== '';
    }

    return false;
  }
  public function allFiles(): array
  {
    return $this->files;
  }
  /**
   * Chuẩn hóa các file từ $_FILE thành 1 định dạng thống nhất
   * @param array $files
   * @return array
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
?>