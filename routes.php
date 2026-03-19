<?php

use App\Controllers\DashboardController;
use App\Controllers\SiteController;
use App\Controllers\StudentController;
use App\Controllers\StudentImportController;
use App\Controllers\TeacherController;
use App\Controllers\CategoryController;

$router->get('/', [SiteController::class, 'index']);
$router->get('/admin', [DashboardController::class, 'index']);

// Students
$router->get('/admin/students', [StudentController::class, 'index']);
$router->get('/admin/students/create', [StudentController::class, 'create']);
$router->post('/admin/students', [StudentController::class, 'store']);
$router->get('/admin/students/import', [StudentController::class, 'import']);
$router->post('/admin/students/import', [StudentImportController::class, 'store']);
$router->get('/admin/students/{id}', [StudentController::class, 'edit']);
$router->post('/admin/students/{id}', [StudentController::class, 'update']);
$router->post('/admin/students/delete/{id}', [StudentController::class, 'destroy']);

// Teachers
$router->get('/admin/teachers', [TeacherController::class, 'index']);
$router->get('/admin/teachers/create', [TeacherController::class, 'create']);
$router->post('/admin/teachers', [TeacherController::class, 'store']);
$router->get('/admin/teachers/{id}', [TeacherController::class, 'edit']);
$router->post('/admin/teachers/{id}', [TeacherController::class, 'update']);
$router->post('/admin/teachers/delete/{id}', [TeacherController::class, 'destroy']);

// Categories
$router->get('/admin/categories', [CategoryController::class, 'index']);
$router->get('/admin/categories/create', [CategoryController::class, 'create']);
$router->post('/admin/categories', [CategoryController::class, 'store']);
$router->get('/admin/categories/{id}', [CategoryController::class, 'edit']);
$router->post('/admin/categories/{id}', [CategoryController::class, 'update']);
$router->post('/admin/categories/delete/{id}', [CategoryController::class, 'destroy']);