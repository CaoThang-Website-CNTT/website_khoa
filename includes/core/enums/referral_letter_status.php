<?php

namespace App\Enums;

final class ReferralLetterStatus
{
  public const PENDING = 'pending';
  public const APPROVED = 'approved';
  public const PRINTED = 'printed';
  public const REJECTED = 'rejected';
  public const CANCELLED = 'cancelled';

  public static function all(): array
  {
    return [self::PENDING, self::APPROVED, self::PRINTED, self::REJECTED, self::CANCELLED];
  }
}
