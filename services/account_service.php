<?php

namespace App\Services;

require_once BASE_PATH . '/stores/account_store.php';

use App\Models\Account;
use App\Stores\{AccountStore};

interface IAccountService
{
  /**
   * Đăng nhập.
   * Cần so sánh mật khẩu gốc (plaintext) với mật khẩu đã băm (hash) trong DB.
   * * @param string $email
   * @param string $rawPassword Mật khẩu chưa qua mã hóa
   * @return Account|null Trả về đối tượng Account nếu đăng nhập thành công, null nếu thất bại.
   */
  public function login(string $email, string $rawPassword): ?Account;

  /**
   * Tạo tài khoản mới.
   * BẮT BUỘC phải hash mật khẩu ($rawPassword) trước khi lưu vào database.
   * * @return int ID của tài khoản vừa được tạo trong database.
   */
  public function createAccount(string $email, string $rawPassword, string $role): int;
  /**
   * Dành cho người dùng (Sinh viên/Giảng viên) tự đổi mật khẩu của chính mình.
   */
  public function changePassword(int $accountId, string $oldPassword, string $newPassword): bool;

  /**
   * Dành cho Admin đặt lại mật khẩu cho người dùng.
   */
  public function forceResetPassword(int $accountId, string $newPassword): bool;

  /**
   * Lấy thông tin tài khoản dựa theo Database Id.
   */
  public function getById(int $accountId): ?Account;

  /**
   * Lấy thông tin tài khoản dựa theo Email.
   */
  public function getByEmail(string $email): ?Account;

  /**
   * Kiểm tra xem Email đã được sử dụng hay chưa (Tránh trùng lặp tài khoản).
   * * @param int|null $excludeAccountId Truyền vào ID của tài khoản đang thao tác để bỏ qua chính nó 
   * @return bool Trả về true nếu email chưa ai dùng, false nếu đã tồn tại.
   */
  public function isEmailUnique(string $email, ?int $excludeAccountId = null): bool;

  /**
   * Vô hiệu hóa tài khoản (Soft Delete).
   */
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

    $hashedOldPassword = password_hash($oldPassword, PASSWORD_DEFAULT);
    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    if ($account->password_hash !== $hashedOldPassword) {
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