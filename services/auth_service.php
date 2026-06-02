<?php

namespace App\Services;

use App\Models\Account;
use App\Stores\{AccountStore};
use Exception;

interface IAuthService
{
  public function authenticate(string $email, string $rawPassword): ?Account;
  public function authenticateOAuthUser(array $oauthData): array;
}

class AuthService implements IAuthService
{
  private AccountStore $_accountStore;
  public function __construct(AccountStore $accountStore)
  {
    $this->_accountStore = $accountStore;
  }
  public function authenticate(string $email, string $rawPassword): ?Account
  {
    $account = $this->_accountStore->findByEmail($email);

    if (!$account) {
      return null;
    }

    if (!password_verify($rawPassword, $account->password_hash)) {
      return null;
    }

    return $account;
  }
  public function authenticateOAuthUser(array $oauthData): array
  {
    // Check email hợp lệ
    if (!$this->isEmailValid($oauthData["email"])) {
      throw new Exception("Email không hợp lệ");
    }

    // Kiểm tra lần đầu đăng nhập
    // Chưa có tài khoản trong CSDL
    $user = $this->_accountStore->findByEmail($oauthData["email"]);

    if (!$user) {
      // Lấy ra role của user dựa trên email
      $role = $this->determineRoleByOAuthEmail($oauthData['email']);
      return [
        "user" => null,
        "role" => $role,
        "is_new" => true
      ];
    }

    return [
      "user" => $user,
      "role" => $user->role,
      "is_new" => false
    ];
  }

  /**
   * Xác định Role dựa trên định dạng email của trường.
   * 
   * Chỉ dùng cho kiểm tra tài khoản Google của trường
   * Account thường không sử dụng method này.
   */
  public function determineRoleByOAuthEmail(string $email): string
  {
    // Lấy phần username trước ký tự @
    $username = strstr($email, '@', true);

    if (!$username) {
      return 'none';
    }

    // Kiểm tra Student: Phải là số và đúng 10 ký tự
    if (ctype_digit($username) && strlen($username) === 10) {
      return 'student';
    }

    // Kiểm tra Teacher: Là chuỗi (chữ cái), có thể bao gồm dấu chấm (vd: nva.it)
    // Regex này kiểm tra chuỗi chỉ chứa chữ cái a-z và dấu chấm
    if (preg_match('/^[a-z.]+$/i', $username)) {
      return 'teacher';
    }

    return 'none';
  }
  public function isEmailValid(string $email): bool
  {
    $pattern = '/^([0-9]{10}|[a-z.]+)@caothang\.edu\.vn$/i';
    return (bool) preg_match($pattern, $email);
  }
}