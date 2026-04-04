<?php
namespace App\Services;

class GoogleOAuthService
{
  private array $scope = [
    "https://www.googleapis.com/auth/userinfo.email",
    "https://www.googleapis.com/auth/userinfo.profile"
  ];
  public function getAuthUrl(): string
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    $_SESSION['oauth_state'] = bin2hex(random_bytes(16));

    $params = [
      'response_type' => 'code',
      'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
      'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI'],
      'scope' => implode(', ', $this->scope),
      'state' => $_SESSION['oauth_state'],
      'access_type' => 'online'
    ];

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
  }

  public function authenticate(string $code): ?array
  {
    // 1. Exchange code for access token
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
      'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
      'client_secret' => $_ENV['GOOGLE_REDIRECT_URI'],
      'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI'],
      'grant_type' => 'authorization_code',
      'code' => $code
    ]));

    $tokenResponse = curl_exec($ch);
    $tokenData = json_decode($tokenResponse, true);

    if (!isset($tokenData['access_token'])) {
      return null; // Handle error appropriately in production
    }

    // 2. Fetch User Profile
    $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);

    $userResponse = curl_exec($ch);

    return json_decode($userResponse, true);
  }
}