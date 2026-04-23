<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Stores\InternshipAssignmentStore;

class InternshipAssignmentController extends Controller
{
  private InternshipAssignmentStore $_store;

  public function __construct(InternshipAssignmentStore $store) {
    $this->_store = $store;
  }

  public function index($batchId, Request $request)
  {
    $this->render("admin/internship_assignments/index", [
      'batchId' => $batchId
    ], layout: "dashboard_layout");
  }
}
