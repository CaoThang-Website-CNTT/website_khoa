<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Controller;
use App\Core\Validator;
use App\Services\MediaService;
use Exception;

class MediaApiController extends Controller
{
  private MediaService $_mediaService;
  public function __construct(MediaService $mediaService)
  {
    $this->_mediaService = $mediaService;
  }
}