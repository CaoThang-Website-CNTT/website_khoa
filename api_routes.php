<?php
use App\Controllers\Api\{StudentApiController, InternshipAssignmentApiController, InternshipBatchApiController};

$router->prefix('api')->group(function ($router) {
  $router->prefix('v1')->group(function ($router) {
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
      
      $router->get('/{id}/assignments', [InternshipAssignmentApiController::class, 'getAssignments']);
      $router->get('/{id}/supervisors', [InternshipAssignmentApiController::class, 'getSupervisors']);
      $router->post('/{id}/auto-assign', [InternshipAssignmentApiController::class, 'autoAssign']);
      $router->post('/{id}/bulk-save', [InternshipAssignmentApiController::class, 'bulkSave']);
    });
  });
});