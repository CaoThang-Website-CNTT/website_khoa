<?php

namespace App\Cms;

final class CmsPageState
{
  public const DRAFT = 'draft';
  public const PUBLISHED = 'published';

  public static function fromAction(mixed $action): string
  {
    $status = is_string($action) ? trim($action) : '';
    if (!in_array($status, [self::DRAFT, self::PUBLISHED], true)) {
      throw new \InvalidArgumentException('Trạng thái CMS page không hợp lệ.');
    }

    return $status;
  }
}
