<?php

namespace App\Controllers;

require_once BASE_PATH . "/includes/core/controller.php";

use App\Core\Controller;

class DashboardController extends Controller
{
  public function index()
  {
    $this->render("admin/index", layout: "dashboard_layout");
  }
}
