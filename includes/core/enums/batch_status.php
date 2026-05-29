<?php

namespace App\Enums;

class BatchStatus
{
    // Database Statuses (Stored Status)
    public const DRAFT = 'draft';
    public const PUBLISHED = 'published';
    public const CLOSED = 'closed';

    // Timeline Statuses (Effective Status)
    public const UPCOMING = 'upcoming';
    public const ACTIVE = 'active';
    public const ENDED = 'ended';

    /**
     * Get all status metadata for mapping
     */
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
            self::ACTIVE => [
                'label' => 'Đang diễn ra',
                'variant' => 'primary'
            ],
            self::ENDED => [
                'label' => 'Đã kết thúc',
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

    public static function getEffectiveOptions(): array
    {
        $meta = self::getMetadata();
        $options = [['label' => 'Tất cả', 'value' => '']];
        
        $order = [self::DRAFT, self::UPCOMING, self::ACTIVE, self::ENDED, self::CLOSED];
        foreach ($order as $key) {
            $options[] = [
                'label' => $meta[$key]['label'],
                'value' => $key
            ];
        }
        
        return $options;
    }
}
