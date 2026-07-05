<?php

namespace App\Core;

abstract class Controller
{
  private static ?Layout $layout = null;
  public function __construct()
  {
  }
  protected function response($data = '', $status = 200, $headers = []): Response
  {
    return new Response($data, $status, $headers);
  }
  protected function json($data, $status = 200, string $message = ""): JsonResponse
  {
    return new JsonResponse($data, $message, $status);
  }
  protected function redirect(string $url, int $status = 302): never
  {
    header("Location: " . url($url), true, $status);
    exit;
  }

  protected function abort(int $code): never
  {
    http_response_code($code);
    exit;
  }

  private static function layout(): Layout
  {
    return self::$layout ??= new Layout();
  }

  protected function render(string $template, array $data = [], ?string $layout = null): void
  {
    $layoutFile = $layout;

    extract($data);

    $layout = self::layout();
    $layout->reset();

    if ($layoutFile) {
      ob_start();
      require BASE_PATH . "/templates/pages/{$template}.php";
      $content = ob_get_clean();

      require BASE_PATH . "/templates/layouts/{$layoutFile}.php";
    } else {
      require BASE_PATH . "/templates/pages/{$template}.php";
    }
  }
  protected function validate(Request $request, array $rules): array
  {
    $validator = new RequestValidator();

    if (!$validator->validate($request->all(), $rules)) {
      $request->session()->flashErrors($validator->getErrors());
      $request->flashOldInputs();

      throw new \Exception();
    }

    return array_intersect_key($request->all(), $rules);
  }
}
