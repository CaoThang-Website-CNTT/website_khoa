<?php

use App\Controllers\Api\{AccountApiController, MediaApiController, StudentApiController, CarouselApiController, MenuApiController, InternshipAssignmentApiController, InternshipBatchApiController, CompanyApiController, InternshipBatchManagementApiController, PostApiController, TeacherDashboardApiController, ExportApiController, ClassroomApiController, ProjectBatchApiController, ProjectTopicApiController};
use App\Core\Router;

$router->prefix('api')->group(function ($router) {
  $router->prefix('v1')->group(function ($router) {
    $router->prefix('accounts')->group(function ($router) {
      $router->get('/', [AccountApiController::class, 'index']);
    });

    $router->prefix('classrooms')->group(function ($router) {
      $router->get('/', [ClassroomApiController::class, 'index']);
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
      $router->post('/parse-import', [InternshipBatchApiController::class, 'parseImport']);
      $router->get('/teachers-active', [InternshipBatchApiController::class, 'getActiveTeachers']);
      $router->post('/', [InternshipBatchApiController::class, 'store']);

      $router->prefix('{id}/management')->group(function ($router) {
        $router->get('/students', [InternshipBatchManagementApiController::class, 'getStudents']);
        $router->post('/students', [InternshipBatchManagementApiController::class, 'addStudent']);
        $router->delete('/students/{student_id}', [InternshipBatchManagementApiController::class, 'removeStudent']);

        $router->get('/supervisors', [InternshipBatchManagementApiController::class, 'getSupervisors']);
        $router->post('/supervisors', [InternshipBatchManagementApiController::class, 'addSupervisor']);
        $router->put('/supervisors/{teacher_id}', [InternshipBatchManagementApiController::class, 'updateSupervisor']);
        $router->delete('/supervisors/{teacher_id}', [InternshipBatchManagementApiController::class, 'removeSupervisor']);

        $router->get('/search-eligible-students', [InternshipBatchManagementApiController::class, 'searchStudents']);
        $router->get('/search-eligible-teachers', [InternshipBatchManagementApiController::class, 'searchTeachers']);

        $router->post('/referral-letters/bulk-action', [InternshipBatchManagementApiController::class, 'bulkActionReferralLetters']);
        $router->post('/referral-letters/{letterId}/receive', [InternshipBatchManagementApiController::class, 'receiveReferralLetter']);
      });

      $router->get('/{id}/assignments', [InternshipAssignmentApiController::class, 'getAssignments']);
      $router->get('/{id}/supervisors', [InternshipAssignmentApiController::class, 'getSupervisors']);
      $router->post('/{id}/auto-assign', [InternshipAssignmentApiController::class, 'autoAssign']);
      $router->post('/{id}/bulk-save', [InternshipAssignmentApiController::class, 'bulkSave']);
    });

    $router->prefix('project_batches')->group(function ($router) {
      $router->get('/teachers-available', [ProjectBatchApiController::class, 'getAvailableTeachers']);
      $router->post('/', [ProjectBatchApiController::class, 'store']);
      $router->get('/{id}/topics', [ProjectTopicApiController::class, 'indexByBatch']);
    });

    $router->prefix('project_topics')->group(function ($router) {
      $router->post('/{id}/approve', [ProjectTopicApiController::class, 'approve']);
      $router->post('/{id}/reject', [ProjectTopicApiController::class, 'reject']);
      $router->post('/bulk-approve', [ProjectTopicApiController::class, 'bulkApprove']);
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

    // Posts
    $router->prefix('posts')->group(function (Router $router) {
      $router->get('/', [PostApiController::class, 'index']);
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

    // Teacher
    $router->prefix('teacher')->group(function (Router $router) {
      $router->get('/submissions/{submissionId}/preview', [TeacherDashboardApiController::class, 'previewSubmission']);
    });

    // Companies
    $router->prefix('companies')->group(function (Router $router) {
      $router->get('/suggest-by-name', [CompanyApiController::class, 'suggestByName']);
      $router->get('/search-merge', [CompanyApiController::class, 'searchForMerge']);
    });

    // Export
    $router->post('/export', [ExportApiController::class, 'export']);
  });
});
