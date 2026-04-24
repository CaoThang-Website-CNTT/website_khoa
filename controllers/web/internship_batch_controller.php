<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\InternshipBatchService;
use App\Core\Request;

class InternshipBatchController extends Controller
{
  private InternshipBatchService $_internshipBatchService;

  public function __construct(InternshipBatchService $internshipBatchService)
  {
    $this->_internshipBatchService = $internshipBatchService;
  }

  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;
    $data = $this->_internshipBatchService->getBatches($currentPage, 15);

    $this->render("admin/internship_batches/index", [
      'data' => $data
    ], layout: "dashboard_layout");
  }

  public function create()
  {
    $this->render("admin/internship_batches/create", [], layout: "dashboard_layout");
  }

  public function show($id)
  {
    // Placeholder for detail page
    echo "Trang chi tiết đợt thực tập #$id đang được phát triển.";
  }
}
