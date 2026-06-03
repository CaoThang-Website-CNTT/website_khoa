<?php
namespace App\Middlewares;

use App\Core\Middleware\BaseMiddleware;
use App\Core\Request;
use App\Core\Session;
use Closure;

class VerifyAuth extends BaseMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    if (!$request->session()->isAuthenticated()) {
      $this->redirect('/login');
    }

    return $next($request);
  }
}