<?php

namespace App\Core;

class Layout
{
  private array $sections = [];
  private array $stack = [];

  public function start(string $name): void
  {
    $this->stack[] = $name;
    ob_start();
  }

  public function end(): void
  {
    $name = array_pop($this->stack);
    $this->sections[$name] = ob_get_clean();
  }

  public function yield(string $name, string $default = ''): string
  {
    return $this->sections[$name] ?? $default;
  }

  public function reset(): void
  {
    $this->sections = [];
    $this->stack = [];
  }
}