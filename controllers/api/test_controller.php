<?php

namespace App\Controllers\Api;

use App\Core\Controller;

class TestController extends Controller
{
  public function index()
  {
    return json_decode(json_encode("hello world"));
  }
}