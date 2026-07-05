<?php

namespace App\Enums;

final class ReferralLetterStatus
{
  public const PENDING = 'pending';
  public const APPROVED = 'approved';
  public const COMPLETED = 'completed';
  public const RECEIVED = 'received';
  public const REJECTED = 'rejected';
  public const CANCELLED = 'cancelled';

  public static function all(): array
  {
    return [self::PENDING, self::APPROVED, self::COMPLETED, self::RECEIVED, self::REJECTED, self::CANCELLED];
  }
}
