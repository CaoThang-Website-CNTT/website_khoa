<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\DashboardStatsService;

class DashboardController extends Controller
{
  public function index()
  {
    $dashboardStatsService = new DashboardStatsService();

    $this->render("admin/index", [
      'overview' => $dashboardStatsService->getOverview(),
    ], layout: "dashboard_layout");
  }
}
