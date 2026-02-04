<?php

class UserModel
{
  // Mock Data: giả lập danh sách sinh viên trong database
  private $mockUsers = [
    [
      'username' => '0306231001',
      'password' => '123456',
      'fullname' => 'Nguyễn Văn A'
    ],
    [
      'username' => '0306231002',
      'password' => 'password',
      'fullname' => 'Trần Thị B'
    ]
  ];

  /**
   * Kiểm tra thông tin đăng nhập
   * @param string $username
   * @param string $password
   * @return array|bool Trả về thông tin user nếu đúng, false nếu sai
   */
  public function checkCredentials($username, $password)
  {
    foreach ($this->mockUsers as $user) {
      if ($user['username'] === $username && $user['password'] === $password) {
        return $user;
      }
    }
    return false;
  }
}
