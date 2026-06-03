<?php

namespace App\Services;

require_once BASE_PATH . '/stores/account_store.php';

use App\Models\Account;
use App\Stores\{AccountStore};
use App\Core\Pageable;

interface IAccountService
{
  // C
  public function createAccount(string $email, string $rawPassword, string $role): ?Account;

  // R
  public function getAccounts(int $page, int $limit = 15): Pageable;
  public function getAccount(int $account_id): Account;
  /** @return Account[] */
  public function getAllAdmins(): array;
  public function getById(int $accountId): ?Account;
  public function getByEmail(string $email): ?Account;
  public function isEmailUnique(string $email, ?int $excludeAccountId = null): bool;
  public function deactivateAccount(int $accountId): bool;
  public function isAdminExists(int $accountId): bool;
  public function updateAccount(int $id, string $email, ?string $rawPassword, string $role): bool;
}

class AccountService implements IAccountService
{
  private AccountStore $_accountStore;
  public function __construct(AccountStore $accountStore)
  {
    $this->_accountStore = $accountStore;
  }
  // C
  public function createAccount(string $email, string $rawPassword, string $role): ?Account
  {
    if (!$this->_accountStore->isEmailUnique($email)) {
      return null;
    }

    $hashedNewPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

    return $this->_accountStore->create($email, $hashedNewPassword, $role);
  }

  // R
  public function getAccounts(int $page, int $limit = 15, string $search = '%'): Pageable
  {
    $accounts = $this->_accountStore->getPaginated($page, $limit, $search);
    $total = $this->_accountStore->getTotalCount();

    return new Pageable($accounts, $total, $limit, $page);
  }

  public function getAccount(int $id): Account
  {
    $account = $this->_accountStore->getById($id)
      ?? throw new \RuntimeException("Tài khoản #{$id} không tồn tại.");

    return $account;
  }
  public function getAllAdmins(): array
  {
    return $this->_accountStore->getAllByRole('admin');
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
  public function isAdminExists(int $accountId): bool
  {
    if ($accountId <= 0) {
      return false;
    }

    return $this->_accountStore->existsWithRole($accountId, 'admin');
  }
  public function updateAccount(int $id, string $email, ?string $rawPassword, string $role): bool
  {
    $hash = null;
    if ($rawPassword && trim($rawPassword) !== '') {
      $hash = password_hash($rawPassword, PASSWORD_DEFAULT);
    }
    return $this->_accountStore->updateAccount($id, $email, $hash, $role);
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