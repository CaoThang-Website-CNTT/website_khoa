<?php

namespace App\Controllers;

require_once BASE_PATH . "/includes/core/controller.php";

use App\Core\Controller;
use App\Services\MenuService;

class SiteController extends Controller
{
  private $_menuService;

  public function __construct(MenuService $menuService)
  {
    $this->_menuService = $menuService;
  }
  public function index()
  {
    $menu = $this->_menuService->getItemsTree(
      $this->_menuService->getMenuByKey('main_nav')->id
    );

    $this->render("site/landing", [
      'menu' => $menu
    ]);
  }
}
