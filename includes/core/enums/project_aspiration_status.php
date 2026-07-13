<?php

namespace App\Enums;

class ProjectAspirationStatus
{
  public const PENDING = 'pending';
  public const APPROVED = 'approved';
  public const REJECTED = 'rejected';

  public static function getMetadata(): array
  {
    return [
      self::PENDING => [
        'label' => 'Đang chờ',
        'variant' => 'warning'
      ],
      self::APPROVED => [
        'label' => 'Được duyệt',
        'variant' => 'success'
      ],
      self::REJECTED => [
        'label' => 'Không được duyệt',
        'variant' => 'destructive'
      ]
    ];
  }

  public static function getLabel(string $status): string
  {
    return self::getMetadata()[$status]['label'] ?? $status;
  }

  public static function getVariant(string $status): string
  {
    return self::getMetadata()[$status]['variant'] ?? 'secondary';
  }
}
