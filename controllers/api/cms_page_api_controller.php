<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\CmsPageService;
use Exception;

class CmsPageApiController extends Controller
{
  public function __construct(private CmsPageService $service) {}

  public function index(Request $request)
  {
    try {
      return $this->json($this->service->getPages(
        max(1, (int)$request->query('page', 1)),
        min(100, max(1, (int)$request->query('limit', 15))),
        ['search' => trim((string)$request->query('search', ''))]
      ), 200);
    } catch (Exception $e) {
      return $this->json(null, 500, $e->getMessage());
    }
  }
}
