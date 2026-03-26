<?php

namespace App\Services;

require_once BASE_PATH . '/stores/account_store.php';

use App\Models\Account;
use App\Stores\{AccountStore};

interface IAccountService
{
  public function login(string $email, string $rawPassword): ?Account;
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
  public function login(string $email, string $rawPassword): ?Account
  {
    $account = $this->_accountStore->findByEmail($email);

    if ($account === null) {
      return null;
    }

    if (password_verify($rawPassword, $account->password_hash)) {
      return $account;
    }
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
}