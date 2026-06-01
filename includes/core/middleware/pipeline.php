<?php
namespace App\Core;

use Closure;

class Pipeline
{
  protected array $middlewares = [];
  protected mixed $passable;
  public function send($passable): static
  {
    $this->passable = $passable;

    return $this;
  }
  public function through(array $middlewares): static
  {
    $this->middlewares = $middlewares;

    return $this;
  }
  public function then(Closure $destination)
  {
    $pipeline = array_reduce(
      array_reverse($this->middlewares),
      $this->getSlice(),
      $destination
    );

    return $pipeline($this->passable);
  }
  protected function getSlice(): Closure
  {
    return function ($nextLayer, $middleware) {
      return function ($request) use ($nextLayer, $middleware) {
        $instance = is_string($middleware) ? new $middleware() : $middleware;
        return $instance->handle($request, $nextLayer);
      };
    };
  }
}