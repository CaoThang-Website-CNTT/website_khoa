<?php

namespace App\Core;

class JsonResponse extends Response
{
  public function __construct(
    mixed $data = null,
    string $message = '',
    int $status = 200,
    array $headers = []
  ) {
    $requestId = bin2hex(random_bytes(8));

    $headers += [
      'Content-Type' => 'application/json; charset=utf-8',
      'Cache-Control' => 'no-store',
      'X-Request-Id' => $requestId,
    ];

    $body = $data instanceof Pageable
      ? $data->jsonSerialize()
      : ['data' => $data];

    $payload = array_merge([
      'success' => $status >= 200 && $status < 300,
      'message' => $message ?: ($status >= 400 ? 'Đã có lỗi xảy ra' : 'Thành công'),
      'timestamp' => date('c'),
    ], $body);

    parent::__construct(
      json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
      $status,
      $headers
    );
  }
}