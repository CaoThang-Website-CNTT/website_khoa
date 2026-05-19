<?php
namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Session;
use Closure;

class StartSession extends BaseMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    $session = new Session();
    $request->setSession($session);

    // Lưu vết lịch sử URL (chỉ áp dụng cho request GET thông thường, không phải AJAX)
    if ($request->method() === 'GET' && !$request->isAjax()) {
      $currentUrl = $this->getCurrentUrl($request);
      
      $prevUrlSession = $session->get('_url.current');
      if ($prevUrlSession && $prevUrlSession !== $currentUrl) {
        $session->put('_url.previous', $prevUrlSession);
      }
      $session->put('_url.current', $currentUrl);
    }

    $response = $next($request);

    return $response;
  }

  private function getCurrentUrl(Request $request): string
  {
    $server = $_SERVER;
    $protocol = (!empty($server['HTTPS']) && $server['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $server['HTTP_HOST'] ?? 'localhost';
    $uri = $server['REQUEST_URI'] ?? '/';
    return $protocol . '://' . $host . $uri;
  }
}