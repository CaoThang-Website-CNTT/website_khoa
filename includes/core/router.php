<?php

namespace App\Core;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class Router
{
  /**
   * Danh sách các route đã được đăng ký
   * @var array
   */
  private array $routes = [];

  // =========================================================================
  // Đăng ký Route
  // =========================================================================

  /**
   * Đăng ký một route với HTTP method GET
   * @param string $path Đường dẫn URI. VD: '/students/{id}'
   * @param array $action Mảng [ControllerClass, 'methodName']
   * @return void
   */
  public function get(string $path, array $action): void
  {
    $this->addRoute('GET', $path, $action);
  }

  /**
   * Đăng ký một route với HTTP method POST
   * @param string $path Đường dẫn URI. VD: '/students'
   * @param array $action Mảng [ControllerClass, 'methodName']
   * @return void
   */
  public function post(string $path, array $action): void
  {
    $this->addRoute('POST', $path, $action);
  }

  /**
   * Đăng ký một route với HTTP method PUT
   * @param string $path Đường dẫn URI. VD: '/students/{id}'
   * @param array $action Mảng [ControllerClass, 'methodName']
   * @return void
   */
  public function put(string $path, array $action): void
  {
    $this->addRoute('PUT', $path, $action);
  }

  /**
   * Đăng ký một route với HTTP method DELETE
   * @param string $path Đường dẫn URI. VD: '/students/{id}'
   * @param array $action Mảng [ControllerClass, 'methodName']
   * @return void
   */
  public function delete(string $path, array $action): void
  {
    $this->addRoute('DELETE', $path, $action);
  }

  /**
   * Biên dịch URI pattern thành regex và lưu route vào danh sách.
   * Các tham số động dạng {param} được chuyển thành capture group trong regex,
   * tên tham số được lưu vào $paramNames theo đúng thứ tự để map sau khi match
   * @param string $method HTTP method. VD: 'GET', 'POST'
   * @param string $path URI pattern. VD: '/students/{id}'
   * @param array $action Mảng [ControllerClass, 'methodName']
   * @return void
   */
  private function addRoute(string $method, string $path, array $action): void
  {
    $paramNames = [];

    // Thay thế các tham số động bằng biểu thức chính quy
    $regex = preg_replace_callback(
      '/\{([a-zA-Z0-9_]+)\}/',
      function (array $m) use (&$paramNames): string {
        $paramNames[] = $m[1];
        return '([a-zA-Z0-9_-]+)';
      },
      $path
    );

    $this->routes[] = [
      'method' => strtoupper($method),
      'regex' => '#^' . $regex . '$#',
      'controller' => $action[0],
      'action' => $action[1],
      'paramNames' => $paramNames,
    ];
  }

  // =========================================================================
  // Dispatch
  // =========================================================================

  /**
   * Xử lý request đến, tìm route phù hợp và gọi controller method tương ứng.
   * Phân biệt 404 (không tìm thấy URI) và 405 (URI đúng nhưng sai method)
   * @param Request $request Request hiện tại đã được capture
   * @return void
   */
  public function dispatch(Request $request): void
  {
    $uri = $request->uri();
    $method = $request->method();

    // Cắt route thừa ở đầu khi để dự án trong XAMPP
    // Như /website_khoa — cắt bỏ để giữ route gọn
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    if ($basePath && str_starts_with($uri, $basePath)) {
      $uri = substr($uri, strlen($basePath)) ?: '/';
    }

    $uriMatched = false;

    foreach ($this->routes as $route) {
      // Kiểm tra regex có khớp
      // Không khớp -> skip
      if (!preg_match($route['regex'], $uri, $matches)) {
        continue;
      }

      $uriMatched = true;

      // Lọc method — URI khớp nhưng method sai -> tiếp tục tìm
      if ($route['method'] !== $method) {
        continue;
      }

      // Bỏ full match $matches[0], chỉ lấy dữ liệu của các tham số động
      array_shift($matches);

      // Map 2 mảng $matches + $route[paramNames] để được format [key => value]
      $routeParams = !empty($route['paramNames'])
        ? array_combine($route['paramNames'], $matches)
        : [];

      $controller = $this->resolveController($route['controller']);
      $args = $this->resolveArgs(
        $route['controller'],
        $route['action'],
        $request,
        $routeParams
      );

      call_user_func_array([$controller, $route['action']], $args);
      return;
    }

    // Phân biệt 404 vs 405
    if ($uriMatched) {
      http_response_code(405);
      require BASE_PATH . '/templates/pages/405.php';
    } else {
      http_response_code(404);
      require BASE_PATH . '/templates/pages/404.php';
    }
  }

  // =========================================================================
  // Khởi tạo controller + tiêm phụ thuộc vào constructor
  // =========================================================================

  /**
   * Khởi tạo controller và tự động tiêm các dependency vào constructor.
   * Hỗ trợ tiêm class và default value, gọi đệ quy để resolve dependency lồng nhau
   * @param string $class Tên đầy đủ của controller class (FQCN)
   * @return object Instance của controller đã được tiêm đủ dependency
   * @throws \RuntimeException Nếu class không tồn tại hoặc không thể resolve dependency
   */
  private function resolveController(string $class): object
  {
    // Kiểm tra controller class có được khai báo không
    if (!class_exists($class)) {
      throw new \RuntimeException("Không tìm thấy [{$class}].");
    }

    $rf = new ReflectionClass($class);
    $constructor = $rf->getConstructor();

    // Nếu không có hàm khởi tạo thì tạo object và return
    if (!$constructor) {
      return new $class();
    }

    // Tiêm phụ thuộc — gọi đệ quy để resolve dependency lồng nhau
    $args = [];

    foreach ($constructor->getParameters() as $param) {
      $type = $param->getType();

      if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
        $args[] = $this->resolveController($type->getName());
      } elseif ($param->isDefaultValueAvailable()) {
        $args[] = $param->getDefaultValue();
      } else {
        throw new \RuntimeException(
          "Không thể tiêm \${$param->getName()} vào {$class}::__construct()"
        );
      }
    }

    return $rf->newInstanceArgs($args);
  }

  // =========================================================================
  // Giải quyết tham số của method, tiêm tham số + Request
  // =========================================================================

  /**
   * Resolve danh sách tham số cho một controller method theo thứ tự khai báo.
   * Ưu tiên theo thứ tự: Request injection > class dependency > route param > default value
   * @param string $class Tên đầy đủ của controller class (FQCN)
   * @param string $method Tên method cần gọi
   * @param Request $request Request hiện tại
   * @param array $routeParams Mảng tham số động từ URI. VD: ['id' => '42']
   * @return array Danh sách tham số đã được resolve theo đúng thứ tự
   */
  private function resolveArgs(
    string $class,
    string $method,
    Request $request,
    array $routeParams
  ): array {
    $rf = new ReflectionMethod($class, $method);
    $args = [];

    foreach ($rf->getParameters() as $param) {
      $type = $param->getType();

      if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
        $typeName = $type->getName();

        if ($typeName === Request::class || is_a($typeName, Request::class, true)) {
          $args[] = $request;
          continue;
        }

        $args[] = $this->resolveController($typeName);
        continue;
      }

      // Xử lý tham số route động — kiểm tra bằng tên, không phụ thuộc vào vị trí
      if (array_key_exists($param->getName(), $routeParams)) {
        $value = $routeParams[$param->getName()];
        $args[] = match ($type?->getName()) {
          'int' => (int) $value,
          'float' => (float) $value,
          default => $value,
        };
        continue;
      }

      // Xử lý các tham số có giá trị mặc định
      if ($param->isDefaultValueAvailable()) {
        $args[] = $param->getDefaultValue();
        continue;
      }

      $args[] = null;
    }

    return $args;
  }
}