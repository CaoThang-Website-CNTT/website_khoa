<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use Exception;
use App\Services\ClassroomService;

class ClassroomApiController extends Controller
{
  private ClassroomService $_classroomService;

  public function __construct(ClassroomService $classroomService)
  {
    $this->_classroomService = $classroomService;
  }

  public function index(Request $request)
  {
    $page = (int) $request->query('page', 1);
    $limit = (int) $request->query('limit', 15);
    $search = $request->query("search", '%');

    $filters = [];
    $rawFilters = $request->query('filters');
    if (is_array($rawFilters)) {
      foreach ($rawFilters as $f) {
        if (isset($f['col']) && isset($f['op']) && isset($f['value'])) {
          $filters[] = $f;
        }
      }
    }

    $sort = [];
    $rawSort = $request->query('sort');
    if (is_array($rawSort) && isset($rawSort['col']) && isset($rawSort['dir'])) {
      $sort = [
        'col' => $rawSort['col'],
        'dir' => $rawSort['dir']
      ];
    }

    try {
      $pageable = $this->_classroomService->getClassroomsPaginated($page, $limit, $search, $filters, $sort);

      return $this->json([
        'data' => array_map(function ($classroom) {
          return [
            'id' => $classroom->id,
            'short_name' => $classroom->short_name ?? '',
            'major_name' => $classroom->major->full_name ?? 'Chưa Có',
            'student_count' => $classroom->student_count ?? 0,
            'actions' => '',
          ];
        }, $pageable->getItems()),
        'total' => $pageable->getTotal(),
        'page' => $pageable->getCurrentPage(),
        'limit' => $pageable->getPerPage()
      ], 200);
    } catch (Exception $e) {
      error_log('Lỗi lấy dữ liệu lớp học: ' . $e->getMessage());
      return $this->json(['message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
    }
  }
}
