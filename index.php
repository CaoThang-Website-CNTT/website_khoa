<?php
define('BASE_PATH', __DIR__);

// App
require_once BASE_PATH . '/includes/env_loader.php';
App\EnvLoader::load(BASE_PATH . '/.env.staging');

// Bật chế độ ghi log lỗi
ini_set('log_errors', '1');

// Chỉ định đường dẫn tuyệt đối đến file log local
ini_set('error_log', BASE_PATH . '/tests/debug.log');

require_once __DIR__ . '/startup.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Response;
use App\Core\Pipeline;
use App\Core\Middleware\{StartSession, VerifyCsrfToken};

$request = Request::capture();
$router = new Router();

require_once BASE_PATH . '/routes.php';
require_once BASE_PATH . '/api_routes.php';

$globalMiddlewares = [
  StartSession::class,
  VerifyCsrfToken::class,
];

$dispatchToRouter = function ($req) use ($router) {
  return $router->dispatch($req);
};

try {
  $response = (new Pipeline())
    ->send($request)
    ->through($globalMiddlewares)
    ->then($dispatchToRouter);

  if ($response instanceof Response) {
    $response->send();
  }

  echo $response;

} catch (\Exception $e) {
  error_log($e->getMessage() . "\nStack trace:\n" . $e->getTraceAsString());

  http_response_code(500);

  $appEnv = $_ENV['APP_ENV'] ?: 'production';

  if ($appEnv === 'local') {
    echo "<h1>An error occurred (Environment: local)</h1>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
  } else {
    $custom500Path = BASE_PATH . '/templates/pages/500.php';
    if (file_exists($custom500Path)) {
      require $custom500Path;
    } else {
      echo "<h1>500 Internal Server Error</h1>";
      echo "<p>Something went wrong on our servers. Please try again later.</p>";
    }
  }
}
