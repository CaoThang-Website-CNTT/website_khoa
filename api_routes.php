<?php
use App\Controllers\Api\{AccountApiController, MediaApiController, StudentApiController, CarouselApiController, MenuApiController, InternshipAssignmentApiController, InternshipBatchApiController, CompanyApiController};
use App\Core\Router;

$router->prefix('api')->group(function ($router) {
  $router->prefix('v1')->group(function ($router) {
    $router->prefix('accounts')->group(function ($router) {
      $router->get('/', [AccountApiController::class, 'index']);
    });

    $router->prefix('students')->group(function ($router) {
      $router->get('/', [StudentApiController::class, 'index']);
      $router->post('/', [StudentApiController::class, 'store']);
      $router->get('/{student_id}', [StudentApiController::class, 'show']);
      $router->put('/{student_id}', [StudentApiController::class, 'update']);
    });

    $router->prefix('internship/batches')->group(function ($router) {
      $router->get('/classrooms', [InternshipBatchApiController::class, 'getClassrooms']);
      $router->get('/students-eligible', [InternshipBatchApiController::class, 'getEligibleStudents']);
      $router->post('/validate-students-bulk', [InternshipBatchApiController::class, 'validateStudentsBulk']);
      $router->get('/teachers-active', [InternshipBatchApiController::class, 'getActiveTeachers']);
      $router->post('/', [InternshipBatchApiController::class, 'store']);

      $router->prefix('{id}/management')->group(function ($router) {
        $router->get('/students', [\App\Controllers\Api\InternshipBatchManagementApiController::class, 'getStudents']);
        $router->post('/students', [\App\Controllers\Api\InternshipBatchManagementApiController::class, 'addStudent']);
        $router->delete('/students/{student_id}', [\App\Controllers\Api\InternshipBatchManagementApiController::class, 'removeStudent']);

        $router->get('/supervisors', [\App\Controllers\Api\InternshipBatchManagementApiController::class, 'getSupervisors']);
        $router->post('/supervisors', [\App\Controllers\Api\InternshipBatchManagementApiController::class, 'addSupervisor']);
        $router->put('/supervisors/{teacher_id}', [\App\Controllers\Api\InternshipBatchManagementApiController::class, 'updateSupervisor']);
        $router->delete('/supervisors/{teacher_id}', [\App\Controllers\Api\InternshipBatchManagementApiController::class, 'removeSupervisor']);

        $router->get('/search-eligible-students', [\App\Controllers\Api\InternshipBatchManagementApiController::class, 'searchStudents']);
        $router->get('/search-eligible-teachers', [\App\Controllers\Api\InternshipBatchManagementApiController::class, 'searchTeachers']);

        $router->post('/referral-letters/bulk-action', [\App\Controllers\Api\InternshipBatchManagementApiController::class, 'bulkActionReferralLetters']);
      });

      $router->get('/{id}/assignments', [InternshipAssignmentApiController::class, 'getAssignments']);
      $router->get('/{id}/supervisors', [InternshipAssignmentApiController::class, 'getSupervisors']);
      $router->post('/{id}/auto-assign', [InternshipAssignmentApiController::class, 'autoAssign']);
      $router->post('/{id}/bulk-save', [InternshipAssignmentApiController::class, 'bulkSave']);
    });

    // Media
    $router->prefix('media')->group(function (Router $router) {
      $router->post('/', [MediaApiController::class, 'upload']);
      $router->get('/', [MediaApiController::class, 'index']);
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

    // Menus
    $router->prefix('menus')->group(function (Router $router) {
      $router->post('/{menu_id}/items/sort', [MenuApiController::class, 'sortItems']);
    });

    // Student
    $router->prefix('student')->group(function (Router $router) {
      $router->post('/profile/update', [StudentApiController::class, 'updateProfile']);
      $router->post('/profile/upload-document', [StudentApiController::class, 'uploadDocument']);
    });

    // Companies
    $router->prefix('companies')->group(function (Router $router) {
      $router->get('/suggest-by-name', [CompanyApiController::class, 'suggestByName']);
    });

  });
});
