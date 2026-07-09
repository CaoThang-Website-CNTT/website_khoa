<?php

namespace App\Enums;

class ProjectBatchStatus
{
  // Database Statuses
  public const DRAFT = 'draft';
  public const PUBLISHED = 'published';
  public const CLOSED = 'closed';

  // Timeline Statuses (Effective Status)
  public const UPCOMING = 'upcoming';
  public const TOPIC_PROPOSAL = 'topic_proposal';
  public const REGISTRATION = 'registration';
  public const REVIEWING = 'reviewing';

  public static function getMetadata(): array
  {
    return [
      self::DRAFT => [
        'label' => 'Bản nháp',
        'variant' => 'secondary'
      ],
      self::PUBLISHED => [
        'label' => 'Đã công bố',
        'variant' => 'primary'
      ],
      self::CLOSED => [
        'label' => 'Đã đóng',
        'variant' => 'destructive'
      ],
      self::UPCOMING => [
        'label' => 'Chuẩn bị',
        'variant' => 'warning'
      ],
      self::TOPIC_PROPOSAL => [
        'label' => 'Nộp đề tài',
        'variant' => 'primary'
      ],
      self::REGISTRATION => [
        'label' => 'Đăng ký đề tài',
        'variant' => 'primary'
      ],
      self::REVIEWING => [
        'label' => 'Đang xét duyệt',
        'variant' => 'warning'
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

  public static function getEffectiveOptions(): array
  {
    $meta = self::getMetadata();
    $options = [];

    $order = [self::DRAFT, self::UPCOMING, self::TOPIC_PROPOSAL, self::REGISTRATION, self::REVIEWING, self::CLOSED];
    foreach ($order as $key) {
      if (isset($meta[$key])) {
        $options[] = [
          'label' => $meta[$key]['label'],
          'value' => $key
        ];
      }
    }

    return $options;
  }
}
