<?php
session_start();

require_once __DIR__ . '/startup.php';

use App\Core\Request;
use App\Core\Router;

$request = Request::capture();
$router = new Router();

require_once BASE_PATH . '/routes.php';

try {
  $router->dispatch($request);
} catch (\RuntimeException $e) {
  http_response_code(500);
  echo $e->getMessage();
}
