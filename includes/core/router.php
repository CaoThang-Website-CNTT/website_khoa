<?php

namespace App\Core;
use App\Core\Request;
use ReflectionClass;
use ReflectionMethod;

require_once __DIR__ . '/request.php';


class Router
{
  private array $routes = [];

  public function get(string $path, array $controllerAction): void
  {
    $this->addRoute('GET', $path, $controllerAction);
  }

  public function post(string $path, array $controllerAction): void
  {
    $this->addRoute('POST', $path, $controllerAction);
  }

  private function addRoute(string $method, string $path, array $controllerAction): void
  {
    // Chuyển đổi path thành Regular Expression để bắt tham số động (VD: /students/edit/{id} -> /students/edit/([0-9]+))
    $regexPath = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $path);

    $this->routes[] = [
      'method' => $method,
      'path' => '#^' . $regexPath . '$#',
      'controller' => $controllerAction[0],
      'action' => $controllerAction[1],
    ];
  }

  public function dispatch(string $uri, string $method, $dependencies = [])
  {
    $parsedUrl = parse_url($uri);
    $path = $parsedUrl['path'] ?? '/';
    $request = Request::capture();

    foreach ($this->routes as $route) {
      if ($route['method'] === $method && preg_match($route['path'], $path, $matches)) {
        array_shift($matches);

        $controllerClass = $route['controller'];
        $action = $route['action'];
        $controllerInstance = $this->resolveController($controllerClass, $dependencies);
        $args = $this->resolveArgs($controllerClass, $action, $request, $matches);

        return call_user_func_array([$controllerInstance, $action], $args);
      }
    }

    http_response_code(404);
    echo "404 - Không tìm thấy trang!";
    exit;
  }

  private function resolveController(string $controllerClass, array $dependencies): object
  {
    $rf = new ReflectionClass($controllerClass);
    $constructor = $rf->getConstructor();

    if (!$constructor) {
      return new $controllerClass();
    }

    $args = [];
    foreach ($constructor->getParameters() as $param) {
      $name = $param->getName();
      if (isset($dependencies[$name])) {
        $args[] = $dependencies[$name];
      } elseif ($param->isOptional()) {
        $args[] = $param->getDefaultValue();
      } else {
        throw new \RuntimeException("Không thể resolve dependency: \${$name} cho {$controllerClass}");
      }
    }

    return $rf->newInstanceArgs($args);
  }

  private function resolveArgs(string $class, string $method, Request $request, array $matches): array
  {
    // Đọc định nghĩa phương thức của class để xác định các tham số cần thiết
    $rf = new ReflectionMethod($class, $method);
    $args = [];
    $matchIndex = 0;

    foreach ($rf->getParameters() as $param) {
      $type = $param->getType();

      // Kiểm tra loại param có phải Request
      if ($type == Request::class || $type === 'Request') {
        $args[] = $request;
      }
      // Param động từ route
      elseif ($matchIndex < count($matches)) {
        $args[] = $matches[$matchIndex++];
      }
      // Kiểm tra param có default value không
      elseif ($param->isDefaultValueAvailable()) {
        $args[] = $param->getDefaultValue();
      } else {
        $args[] = null;
      }
    }

    return $args;
  }
}
