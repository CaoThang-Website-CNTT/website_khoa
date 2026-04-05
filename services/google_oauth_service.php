<?php
namespace App\Services;

use App\Core\Session;

class GoogleOAuthService
{
  private array $scope = [
    "https://www.googleapis.com/auth/userinfo.email",
    "https://www.googleapis.com/auth/userinfo.profile"
  ];

  public function getAuthUrl(Session $session): string
  {
    $state = bin2hex(random_bytes(16));
    $session->put(Session::KEY_OAUTH_STATE, $state);

    $params = [
      'response_type' => 'code',
      'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
      'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI'],
      'scope' => implode(' ', $this->scope),
      'state' => $state,
      'access_type' => 'online'
    ];

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
  }

  public function authenticate(string $code): ?array
  {
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
      'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
      'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
      'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI'],
      'grant_type' => 'authorization_code',
      'code' => $code
    ]));

    $tokenResponse = curl_exec($ch);
    if ($tokenResponse === false) {
      return null;
    }
    $tokenData = json_decode($tokenResponse, true);

    if (!isset($tokenData['access_token'])) {
      return null;
    }

    $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);

    $userResponse = curl_exec($ch);
    if ($userResponse === false) {
      return null;
    }
    $userData = json_decode($userResponse, true);

    return is_array($userData) ? $userData : null;
  }
}
