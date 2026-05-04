<?php
namespace App\Core;

class Session
{
  /** Token CSRF */
  public const KEY_CSRF = '_token';

  /** User đã đăng nhập: ['account_id' => int, 'email' => string, 'role' => string] */
  public const KEY_AUTH_USER = '_auth_user';

  /** OAuth PKCE state (Google redirect) */
  public const KEY_OAUTH_STATE = 'oauth_state';

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
    return $_SESSION[$key] ?? null;
  }

  public function forget(string|array $keys): void
  {
    foreach ((array) $keys as $key) {
      unset($_SESSION[$key]);
    }
  }

  public function csrfToken(): string
  {
    $existing = $this->get(self::KEY_CSRF);
    if (is_string($existing) && $existing !== '') {
      return $existing;
    }
    $token = bin2hex(random_bytes(40));
    $this->put(self::KEY_CSRF, $token);
    return $token;
  }

  /**
   * @return array{account_id: int, email: string, role: string}|null
   */
  public function authUser(): ?array
  {
    $user = $this->get(self::KEY_AUTH_USER);
    if (!is_array($user) || empty($user['account_id'])) {
      return null;
    }
    return [
      'account_id' => (int) $user['account_id'],
      'email' => (string) ($user['email'] ?? ''),
      'role' => (string) ($user['role'] ?? ''),
    ];
  }

  public function isAuthenticated(): bool
  {
    return $this->authUser() !== null;
  }

  public function loginUser(int $accountId, string $email, string $role): void
  {
    if (session_status() === PHP_SESSION_ACTIVE) {
      session_regenerate_id(true);
    }
    $this->put(self::KEY_AUTH_USER, [
      'account_id' => $accountId,
      'email' => $email,
      'role' => $role,
    ]);
  }

  public function logoutUser(): void
  {
    $this->forget(self::KEY_AUTH_USER);
    if (session_status() === PHP_SESSION_ACTIVE) {
      session_regenerate_id(true);
    }
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
  /**
   * Lưu lại dữ liệu form cũ, có loại trừ các field nhạy cảm
   */
  public function flashOldInputs(array $data, array $only = [], array $except = []): void
  {
    $blacklist = ['_token', 'password', 'password_confirmation', 'new_password'];

    if (!empty($only)) {
      $data = array_intersect_key($data, array_flip($only));
    } elseif (!empty($except)) {
      $data = array_diff_key($data, array_flip($except));
    }

    $data = array_diff_key($data, array_flip($blacklist));

    $this->flash('_old_input', $data);
  }
  public function getOldInputs(): array
  {
    return $this->getFlash("_old_input") ?? [];
  }

  public function flashErrors(array $errors): void
  {
    $this->flash('_errors', $errors);
  }

  public function getErrors(): array
  {
    return $this->getFlash('_errors') ?? [];
  }
  public function destroy(): void
  {
    $_SESSION = [];
    session_destroy();
  }
}