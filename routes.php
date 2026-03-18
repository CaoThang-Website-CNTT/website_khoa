<?php
$router->get('/', ['SiteController', 'index']);
$router->get('/admin', ['UserController', 'index']);

$router->get('/admin/users', ['UserController', 'index']);

$router->get('/admin/students/create', ['StudentController', 'create']);
$router->post('/admin/students/store', ['StudentController', 'store']);
$router->get('/admin/students/edit/{id}', ['StudentController', 'edit']);
$router->post('/admin/students/update/{id}', ['StudentController', 'update']);
$router->post('/admin/students/delete/{id}', ['StudentController', 'destroy']);
$router->get('/admin/students/import', ['StudentController', 'import']);
$router->post('/admin/students/import', ['StudentImportController', 'store']);

$router->get('/admin/teachers/create', ['TeacherController', 'create']);
$router->post('/admin/teachers/store', ['TeacherController', 'store']);
$router->get('/admin/teachers/edit/{id}', ['TeacherController', 'edit']);
$router->post('/admin/teachers/update/{id}', ['TeacherController', 'update']);
$router->post('/admin/teachers/delete/{id}', ['TeacherController', 'destroy']);

$router->get('/admin/categories', ['CategoryController', 'index']);
$router->get('/admin/categories/create', ['CategoryController', 'create']);
$router->get('/admin/categories/{id}', ['CategoryController', 'edit']);
$router->post('/admin/categories', ['CategoryController', 'store']);
$router->post('/admin/categories/{id}', ['CategoryController', 'update']);
$router->post('/admin/categories/delete/{id}', ['CategoryController', 'destroy']);