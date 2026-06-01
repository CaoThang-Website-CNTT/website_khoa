<?php
namespace App\Middlewares;

use App\Core\Middleware\BaseMiddleware;
use App\Core\Request;
use Closure;

class VerifyRole extends BaseMiddleware
{
  private array $allowedRoles;

  public function __construct(string ...$roles)
  {
    $this->allowedRoles = $roles;
  }

  public function handle(Request $request, Closure $next): mixed
  {
    // Session đã được VerifyAuth bind trước đó — đọc trực tiếp từ request
    $user = $request->session()->authUser();

    if (!in_array($user['role'], $this->allowedRoles, strict: true)) {
      // Đã đăng nhập nhưng sai role → không redirect /login
      // mà về trang dashboard riêng của role đó
      $this->redirect($this->dashboardFor($user['role']));
    }

    return $next($request);
  }

  private function dashboardFor(string $role): string
  {
    return match ($role) {
      'admin', 'editor', 'super_admin' => '/admin',
      'student' => '/student',
      'teacher' => '/teacher',
      default => '/',
    };
  }
}