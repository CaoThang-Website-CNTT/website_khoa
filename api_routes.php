<?php
use App\Controllers\Api\{StudentApiController};

$router->prefix('api')->group(function ($router) {
  $router->prefix('v1')->group(function ($router) {
    $router->prefix('students')->group(function ($router) {
      $router->get('/', [StudentApiController::class, 'index']);
      $router->post('/', [StudentApiController::class, 'store']);
      $router->get('/{student_id}', [StudentApiController::class, 'show']);
      $router->put('/{student_id}', [StudentApiController::class, 'update']);
    });
  });
});