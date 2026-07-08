<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\TeacherService;
use Exception;

class TeacherApiController extends Controller
{
  public function __construct(private TeacherService $service) {}

  public function index(Request $request)
  {
    try {
      return $this->json($this->service->getTeachers(
        max(1, (int)$request->query('page', 1)),
        min(100, max(1, (int)$request->query('limit', 15)))
      ), 200);
    } catch (Exception $e) {
      return $this->json(null, 500, $e->getMessage());
    }
  }
}
