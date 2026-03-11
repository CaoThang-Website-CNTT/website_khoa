<?php

namespace App\Core;

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
      'action' => $controllerAction[1]
    ];
  }

  public function dispatch(string $uri, string $method, $dependencies = [])
  {
    $parsedUrl = parse_url($uri);
    $path = $parsedUrl['path'] ?? '/';

    foreach ($this->routes as $route) {
      if ($route['method'] === $method && preg_match($route['path'], $path, $matches)) {
        array_shift($matches);
        $controllerClass = $route['controller'];
        $action = $route['action'];

        $controllerInstance = new $controllerClass($dependencies['educationService']);
        return call_user_func_array([$controllerInstance, $action], $matches);
      }
    }

    http_response_code(404);
    echo "404 - Không tìm thấy trang!";
    exit;
  }
}
