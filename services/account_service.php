<?php

namespace App\Services;

require_once BASE_PATH . '/stores/account_store.php';

use App\Models\Account;
use App\Stores\{AccountStore};
use Exception;

interface IAccountService
{
  public function authenticateOAuthUser(array $oauthData): array;
  public function createAccount(string $email, string $rawPassword, string $role): int;
  public function changePassword(int $accountId, string $oldPassword, string $newPassword): bool;
  public function forceResetPassword(int $accountId, string $newPassword): bool;
  public function getById(int $accountId): ?Account;
  public function getByEmail(string $email): ?Account;
  public function isEmailUnique(string $email, ?int $excludeAccountId = null): bool;
  public function deactivateAccount(int $accountId): bool;
}

class AccountService implements IAccountService
{
  private AccountStore $_accountStore;
  public function __construct(AccountStore $accountStore)
  {
    $this->_accountStore = $accountStore;
  }
  public function authenticateOAuthUser(array $oauthData): array
  {
    // Check email hợp lệ
    if (!$this->isEmailValid($oauthData["email"])) {
      throw new Exception("Email không hợp lệ");
    }

    // Kiểm tra lần đầu đăng nhập
    // Chưa có tài khoản trong CSDL
    $user = $this->getByEmail($oauthData["email"]);

    if (!$user) {
      // Lấy ra role của user dựa trên email
      $role = $this->determineRole($oauthData['email']);
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
  public function createAccount(string $email, string $rawPassword, string $role): int
  {
    if (!$this->_accountStore->isEmailUnique($email)) {
      return 0;
    }

    $hashedNewPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

    return $this->_accountStore->create($email, $hashedNewPassword, $role);
  }
  public function changePassword(int $accountId, string $oldPassword, string $newPassword): bool
  {
    $account = $this->_accountStore->getById($accountId);

    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    if (password_verify($oldPassword, $account->password_hash)) {
      return false;
    }

    return $this->_accountStore->updatePassword($accountId, $hashedNewPassword);
  }
  public function forceResetPassword(int $accountId, string $newPassword): bool
  {
    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    return $this->_accountStore->updatePassword($accountId, $hashedNewPassword);

  }
  public function getById(int $accountId): ?Account
  {
    return $this->_accountStore->getById($accountId);
  }
  public function getByEmail(string $email): ?Account
  {
    return $this->_accountStore->findByEmail($email);
  }
  public function isEmailUnique(string $email, ?int $excludeAccountId = null): bool
  {
    return $this->_accountStore->isEmailUnique($email, $excludeAccountId);
  }

  public function deactivateAccount(int $accountId): bool
  {
    return $this->_accountStore->softDelete($accountId);
  }
  /**
   * Xác định Role dựa trên định dạng email của trường.
   */
  public function determineRole(string $email): string
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