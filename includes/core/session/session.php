<?php
namespace App\Core;

class Session
{
  public function __construct()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    if (!isset($_SESSION['_flash'])) {
      $_SESSION['_flash'] = ['old' => [], 'new' => []];
    }

    $_SESSION['_flash']['old'] = $_SESSION['_flash']['new'];
    $_SESSION['_flash']['new'] = [];
  }

  public function put($key, $value)
  {
    $_SESSION[$key] = $value;
  }

  public function get($key)
  {
    return $_SESSION[$key];
  }

  public function flash($key, $value)
  {
    $_SESSION['_flash']['new'][$key] = $value;
  }
  public function getFlash($key)
  {
    return $_SESSION['_flash']['old'][$key] ?? null;
  }
  public function flashNotify(string $type, string $title, string $desc = ""): void
  {
    $this->flash('notification', compact('type', 'title', 'desc'));
  }
  public function flashOldInputs(array $data, array $only = [], array $except = []): void
  {
    if (!empty($only)) {
      $data = array_intersect_key($data, array_flip($only));
    } elseif (!empty($except)) {
      $data = array_diff_key($data, array_flip($except));
    }

    $this->flash('_old_input', $data);
  }
  public function getOldInputs(): array
  {
    return $this->getFlash("_old_input");
  }

  public function flashErrors(array $errors): void
  {
    $this->flash('_errors', $errors);
  }

  public function getErrors(): array
  {
    return $this->getFlash('_errors');
  }
  public function destroy(): void
  {
    $_SESSION = [];
    session_destroy();
  }
}