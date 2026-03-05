<?php
require_once __DIR__ . '/../models/student_interface.php';
require_once __DIR__ . '/../models/mock_student_service.php';
require_once __DIR__ . '/../controllers/student_controller.php';

$mockService = new MockEducationService();
$studentCtrl = new StudentController($mockService);

$students = $studentCtrl->index();
echo "danh sách sinh viên:";
print_r($students);

echo "thông tin sinh viên id = 11:";
$student = $studentCtrl->show(11);
print_r($student);