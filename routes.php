<?php

use App\Core\Router;

$router = new Router();

$router->get('/admin', ['StudentController', 'index']);

$router->get('/admin/students', ['StudentController', 'index']);
$router->get('/admin/students/create', ['StudentController', 'create']);
$router->post('/admin/students/store', ['StudentController', 'store']);
$router->get('/admin/students/edit/{id}', ['StudentController', 'edit']);
$router->post('/admin/students/update/{id}', ['StudentController', 'update']);
$router->post('/admin/students/delete/{id}', ['StudentController', 'destroy']);
$router->get('/admin/students/import', ['StudentController', 'import']);
$router->post('/admin/students/import', ['StudentImportController', 'store']);

$router->get('/admin/teachers', ['TeacherController', 'index']);
$router->get('/admin/teachers/create', ['TeacherController', 'create']);
$router->post('/admin/teachers/store', ['TeacherController', 'store']);
$router->get('/admin/teachers/edit/{id}', ['TeacherController', 'edit']);
$router->post('/admin/teachers/update/{id}', ['TeacherController', 'update']);
$router->post('/admin/teachers/delete/{id}', ['TeacherController', 'destroy']);

return $router;
