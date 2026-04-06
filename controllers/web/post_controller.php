<?php

namespace App\Controllers;

use App\Core\Controller;

class PostController extends Controller
{
  public function __construct(
  ) {
  }
  public function index(): void
  {
    $this->render('admin/posts/create', layout: "dashboard_layout");
  }
}