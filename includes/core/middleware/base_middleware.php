<?php
namespace App\Core\Middleware;

use App\Core\Request;
use Closure;

interface IMiddleware
{
  /**
   * Xử lý request
   *
   * @param  \App\Core\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next);
}
abstract class BaseMiddleware implements IMiddleware
{
  protected function redirect(string $url)
  {
    header('Location: ' . url($url), true, 302);
    exit();
  }
  protected function reject(int $statusCode = 403, string $message = "Forbidden")
  {
    http_response_code($statusCode);
    echo $message;
    exit();
  }
}