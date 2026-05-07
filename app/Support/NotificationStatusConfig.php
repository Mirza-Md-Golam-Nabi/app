<?php

namespace App\Support;

class NotificationStatusConfig
{
    public static function get(string $status): array
    {
        return self::all()[$status] ?? self::all()['info'];
    }

    private static function all(): array
    {
        return [
            'success' => [
                'bg' => '#ECFDF5',
                'color' => '#10B981',
                'icon' => 'heroicon-o-check-circle',
            ],
            'danger' => [
                'bg' => '#FEF2F2',
                'color' => '#EF4444',
                'icon' => 'heroicon-o-x-circle',
            ],
            'info' => [
                'bg' => '#EFF6FF',
                'color' => '#3B82F6',
                'icon' => 'heroicon-o-information-circle',
            ],
            'warning' => [
                'bg' => '#FFFBEB',
                'color' => '#F59E0B',
                'icon' => 'heroicon-o-exclamation-triangle',
            ],
        ];
    }
}
