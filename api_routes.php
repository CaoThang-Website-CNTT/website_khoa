<?php
use App\Controllers\Api\{TestController};

$router->prefix('api')->group(function ($router) {
  $router->prefix('v1')->group(function ($router) {
    $router->get('/', [TestController::class, 'index']);
  });
});