<?php
namespace App\Middlewares;

use App\Core\Middleware\BaseMiddleware;
use App\Core\JsonResponse;
use App\Core\Request;
use Closure;

class VerifyAuth extends BaseMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    if (!$request->session()->isAuthenticated()) {
      if ($request->expectsJson()) {
        return new JsonResponse(null, 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 401);
      }

      $request->session()->flashNotify('warning', 'Vui lòng đăng nhập', 'Bạn cần đăng nhập để truy cập trang quản trị.');
      $this->redirect('/login');
    }

    return $next($request);
  }
}
