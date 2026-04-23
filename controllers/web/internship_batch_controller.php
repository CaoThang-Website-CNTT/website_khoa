<?php

namespace App\Controllers;

use App\Core\Controller;

class InternshipBatchController extends Controller
{
  public function create()
  {
    $this->render("admin/internship_batches/create", [], layout: "dashboard_layout");
  }
}
