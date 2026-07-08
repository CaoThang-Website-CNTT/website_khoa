<?php

namespace App\Enums;

class ProjectTopicStatus
{
  public const DRAFT = 'draft';
  public const PENDING = 'pending';
  public const APPROVED = 'approved';
  public const REJECTED = 'rejected';

  public static function getMetadata(): array
  {
    return [
      self::DRAFT => [
        'label' => 'Bản nháp',
        'variant' => 'secondary'
      ],
      self::PENDING => [
        'label' => 'Chờ duyệt',
        'variant' => 'warning'
      ],
      self::APPROVED => [
        'label' => 'Đã duyệt',
        'variant' => 'success'
      ],
      self::REJECTED => [
        'label' => 'Từ chối',
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

  public static function getOptions(): array
  {
    $meta = self::getMetadata();
    $options = [];

    foreach ($meta as $key => $value) {
      $options[] = [
        'label' => $value['label'],
        'value' => $key
      ];
    }

    return $options;
  }
}
