<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CategoryService;

class PostController extends Controller
{
  private CategoryService $_categoryService;
  public function __construct(
  ) {
  }
  public function index(): void
  {
    $this->render('admin/posts/create', [
      "categories" => []
    ], layout: "canva_layout");
  }
}