<?php

use App\Controllers\SiteController;
use App\Controllers\UserController;
use App\Controllers\StudentController;
use App\Controllers\StudentImportController;
use App\Controllers\TeacherController;
use App\Controllers\CategoryController;

$router->get('/', [SiteController::class, 'index']);
$router->get('/admin', [UserController::class, 'index']);

// Users
$router->get('/admin/users', [UserController::class, 'index']);

// Students
$router->get('/admin/students/create', [StudentController::class, 'create']);
$router->post('/admin/students/store', [StudentController::class, 'store']);
$router->get('/admin/students/edit/{id}', [StudentController::class, 'edit']);
$router->post('/admin/students/update/{id}', [StudentController::class, 'update']);
$router->post('/admin/students/delete/{id}', [StudentController::class, 'destroy']);
$router->get('/admin/students/import', [StudentController::class, 'import']);
$router->post('/admin/students/import', [StudentImportController::class, 'store']);

// Teachers
$router->get('/admin/teachers/create', [TeacherController::class, 'create']);
$router->post('/admin/teachers/store', [TeacherController::class, 'store']);
$router->get('/admin/teachers/edit/{id}', [TeacherController::class, 'edit']);
$router->post('/admin/teachers/update/{id}', [TeacherController::class, 'update']);
$router->post('/admin/teachers/delete/{id}', [TeacherController::class, 'destroy']);

// Categories
$router->get('/admin/categories', [CategoryController::class, 'index']);
$router->get('/admin/categories/create', [CategoryController::class, 'create']);
$router->get('/admin/categories/{id}', [CategoryController::class, 'edit']);
$router->post('/admin/categories', [CategoryController::class, 'store']);
$router->post('/admin/categories/{id}', [CategoryController::class, 'update']);
$router->post('/admin/categories/delete/{id}', [CategoryController::class, 'destroy']);