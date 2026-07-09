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
  /**
   * Validate dữ liệu request theo rules đã định.
   * Tự động sanitize (trim) dữ liệu trước khi validate.
   * Nếu validate thất bại: flash errors + old inputs vào session, throw ValidationException.
   *
   * @param Request $request Request hiện tại
   * @param array $rules Mảng rules. VD: ['title' => ['required', 'max:255']]
   * @return array Dữ liệu đã sanitize, chỉ chứa các key có trong $rules (chống mass-assignment)
   * @throws ValidationException Khi dữ liệu không hợp lệ
   */
  protected function validate(Request $request, array $rules): array
  {
    $validator = new RequestValidator();
    $data = $request->sanitized();

    if (!$validator->validate($data, $rules)) {
      $request->session()->flashErrors($validator->getErrors());
      $request->flashOldInputs();

      throw new ValidationException($validator->getErrors());
    }

    return array_intersect_key($data, $rules);
  }
}
