<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\CompanyService;

class CompanyApiController extends Controller
{
  private CompanyService $_companyService;

  public function __construct(CompanyService $companyService)
  {
    $this->_companyService = $companyService;
  }

  /**
   * Gợi ý công ty theo tên
   * 
   * @param Request $request
   */
  public function suggestByName(Request $request)
  {
    $query = trim($request->query('q', ''));
    if (strlen($query) < 1) {
      return $this->json([]);
    }

    $results = $this->_companyService->suggestByName($query);
    return $this->json($results);
  }

  public function searchForMerge(Request $request)
  {
    $query = trim($request->query('q', ''));
    $excludeId = (int) $request->query('exclude', 0);

    if (strlen($query) < 1) {
      return $this->json([]);
    }

    $results = $this->_companyService->searchForMerge($query, $excludeId);
    return $this->json($results);
  }
}
?>