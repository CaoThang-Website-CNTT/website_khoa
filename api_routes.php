<?php
use App\Controllers\Api\{MediaApiController, StudentApiController, CarouselApiController};
use App\Core\Router;

$router->prefix('api')->group(function ($router) {
  $router->prefix('v1')->group(function ($router) {
    $router->prefix('students')->group(function ($router) {
      $router->get('/', [StudentApiController::class, 'index']);
      $router->post('/', [StudentApiController::class, 'store']);
      $router->get('/{student_id}', [StudentApiController::class, 'show']);
      $router->put('/{student_id}', [StudentApiController::class, 'update']);
    });

    // Media
    $router->prefix('media')->group(function (Router $router) {
      $router->post('/', [MediaApiController::class, 'upload']);
      $router->get('/', [MediaApiController::class, 'indexByPost']);
      $router->get('/{media_id}', [MediaApiController::class, 'show']);
      $router->patch('/{media_id}', [MediaApiController::class, 'updateMetadata']);
      $router->post('/attach', [MediaApiController::class, 'attachToPost']);
      $router->delete('/{media_id}', [MediaApiController::class, 'delete']);
      $router->delete('/orphans', [MediaApiController::class, 'deleteOrphans']);
    });

    // Carousels
    $router->prefix('carousels')->group(function (Router $router) {
      $router->post('/{carousel_id}/slides/sort', [CarouselApiController::class, 'sortSlides']);
    });
  });
});