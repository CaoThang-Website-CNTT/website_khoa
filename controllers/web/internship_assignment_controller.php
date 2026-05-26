<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Stores\{InternshipAssignmentStore, InternshipBatchStore};

class InternshipAssignmentController extends Controller
{
  private InternshipAssignmentStore $_store;
  private InternshipBatchStore $_batchStore;

  public function __construct(
    InternshipAssignmentStore $store,
    InternshipBatchStore $batchStore
  ) {
    $this->_store = $store;
    $this->_batchStore = $batchStore;
  }

  public function index($batchId, Request $request)
  {
    $batch = $this->_batchStore->getById((int)$batchId);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập này');
      return $this->redirect('admin/internship_batches');
    }

    $this->render("admin/internship_assignments/index", [
      'batchId' => $batchId,
      'batch' => (object)$batch
    ], layout: "dashboard_layout");
  }
}
