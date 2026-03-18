<?php

namespace App\Controllers;

class SiteController
{
  public function index()
  {
    require_once __DIR__ . '/../templates/pages/site/landing.php';
  }
}
