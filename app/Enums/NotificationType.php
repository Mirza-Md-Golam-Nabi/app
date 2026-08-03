<?php

namespace App\Enums;

enum NotificationType: string
{
    // Note: This enum intentionally uses PascalCase values instead of the usual snake_case,
    // because Blade converts them to kebab-case for the frontend notification types.
    // This ensures consistency with the existing frontend notification type definitions.

    case OcrCompleted = 'OcrCompleted';

    case PrizeBondWon = 'PrizeBondWon';

    case TaskCompleted = 'TaskCompleted';
}
