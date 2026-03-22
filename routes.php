<?php

use App\Controllers\{DashboardController, MenuController, SiteController, StudentController, StudentImportController, TeacherController, CategoryController, WebSettingsController};

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

// Menus
$router->get('/admin/menus', [MenuController::class, 'index']);
$router->get('/admin/menus/create', [MenuController::class, 'create']);
$router->post('/admin/menus', [MenuController::class, 'store']);
$router->get('/admin/menus/{id}', [MenuController::class, 'edit']);
$router->post('/admin/menus/{id}', [MenuController::class, 'update']);
$router->post('/admin/menus/delete/{id}', [MenuController::class, 'destroy']);

// Menu Items
$router->get('/admin/menus/{menuId}/items/create', [MenuController::class, 'createItem']);
$router->post('/admin/menus/{menuId}/items', [MenuController::class, 'storeItem']);
$router->post('/admin/menus/{menuId}/items/reorder', [MenuController::class, 'reorderItems']);
$router->get('/admin/menus/{menuId}/items/{itemId}', [MenuController::class, 'editItem']);
$router->post('/admin/menus/{menuId}/items/{itemId}', [MenuController::class, 'updateItem']);
$router->post('/admin/menus/{menuId}/items/delete/{itemId}', [MenuController::class, 'destroyItem']);

// Web Settings
$router->get('/admin/web_settings', [WebSettingsController::class, 'index']);
$router->get('/admin/web_settings/create', [WebSettingsController::class, 'create']);
$router->post('/admin/web_settings', [WebSettingsController::class, 'store']);
$router->get('/admin/web_settings/{group}/edit', [WebSettingsController::class, 'edit']);
$router->post('/admin/web_settings/{group}', [WebSettingsController::class, 'batchUpdate']);
$router->post('/admin/web_settings/delete/{id}', [WebSettingsController::class, 'destroy']);