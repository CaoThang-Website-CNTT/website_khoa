<?php
require_once __DIR__ . '/startup.php';

App\EnvLoader::load(BASE_PATH . '/.env.local');

use App\Core\Request;
use App\Core\Router;
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

  echo $response;

} catch (\Exception $e) {
  http_response_code(500);
  echo $e->getMessage();
}