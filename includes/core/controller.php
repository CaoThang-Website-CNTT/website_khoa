<?php

namespace App\Core;

abstract class Controller
{
  public function __construct()
  {
  }
  protected function redirect(string $url): never
  {
    header("Location: " . url($url));
    exit;
  }

  protected function abort(int $code): never
  {
    http_response_code($code);
    exit;
  }

  protected function render(string $template, array $data = [], ?string $layout = null): void
  {
    extract($data);

    if ($layout) {
      ob_start();
      require BASE_PATH . "/templates/pages/{$template}.php";
      $content = ob_get_clean();
      require BASE_PATH . "/templates/layouts/{$layout}.php";
    } else {
      require BASE_PATH . "/templates/pages/{$template}.php";
    }
  }

  protected function json(mixed $data, int $status = 200): never
  {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
  }
}