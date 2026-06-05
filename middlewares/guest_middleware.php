<?php
namespace App\Middlewares;

use App\Core\Middleware\BaseMiddleware;
use App\Core\Request;
use App\Middlewares\Traits\HasDashboardRouting;
use Closure;

class GuestMiddleware extends BaseMiddleware
{
  use HasDashboardRouting;

  public function handle(Request $request, Closure $next): mixed
  {
    // Nếu người dùng ĐÃ đăng nhập
    if ($request->session()->isAuthenticated()) {
      $user = $request->session()->authUser();

      // Chuyển hướng về Dashboard tương ứng với Role của họ
      $this->redirect($this->dashboardFor($user['role'] ?? ''));
    }

    // Nếu CHƯA đăng nhập, cho phép đi tiếp vào trang login
    return $next($request);
  }
}