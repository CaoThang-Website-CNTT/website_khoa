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

    $response = $next($request);

    return $response;
  }
}