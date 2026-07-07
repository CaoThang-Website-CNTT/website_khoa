<?php

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Session;
use Closure;

/**
 * Kiểm tra CSRF cho request thay đổi trạng thái (POST/PUT/PATCH/DELETE),
 * so khớp session token với `_token` trong body
 * hoặc header X-CSRF-TOKEN (tiện cho fetch/AJAX).
 */
class VerifyCsrfToken extends BaseMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    $method = $request->method();
    if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
      return $next($request);
    }

    $session = $request->session();
    $sessionToken = $session->get(Session::KEY_CSRF);
    $token = $request->input('_token')
      ?? $request->json('_token')
      ?? $request->header('X-CSRF-TOKEN')
      ?? $request->header('X-XSRF-TOKEN');

    if (
      !is_string($sessionToken) || $sessionToken === ''
      || !is_string($token) || $token === ''
      || !hash_equals($sessionToken, $token)
    ) {
      http_response_code(419);
      require BASE_PATH . '/templates/pages/419.php';
      exit;
    }

    return $next($request);
  }
}
