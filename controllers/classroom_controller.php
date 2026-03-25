<?php

namespace App\Controllers;

require_once BASE_PATH . "/includes/core/controller.php";
require_once BASE_PATH . '/includes/core/request_validator.php';
require_once BASE_PATH . '/models/classroom.php';
require_once BASE_PATH . '/models/major.php';
require_once BASE_PATH . '/models/specialization.php';

use App\Core\Controller;
use App\Core\Page;
use App\Core\Request;
use App\Models\{Classroom, Major, Specialization};
use App\Core\Validator;
use App\Services\ClassroomService;

class ClassroomController extends Controller
{
  private ClassroomService $_classroomService;

  public function __construct(ClassroomService $classroomService)
  {
    $this->_classroomService = $classroomService;
  }

  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;

    $classrooms = $this->_classroomService->getClassrooms($currentPage);
    $total = $this->_classroomService->getTotalClassroomsCount();

    $page = new Page($total, 15, $currentPage);

    $this->render("admin/classrooms/index", [
      'classrooms' => $classrooms,
      'page' => $page,
    ], layout: "dashboard_layout");
  }

  public function create()
  {
    $classrooms = $this->_classroomService->getAllClassrooms();

    $this->render("admin/classrooms/create", [
      'classrooms' => $classrooms
    ], layout: "dashboard_layout");
  }

  public function store(Request $request)
  {
  }

  public function edit($id)
  {
    $specializations = $this->_classroomService->getSpecializationsByMajorId($id);

    $this->render("admin/classrooms/edit", [
      'specializations' => $specializations
    ], layout: "dashboard_layout");

  }

  public function update($id, Request $request)
  {
  }

  public function destroy($id, Request $request)
  {
  }
}