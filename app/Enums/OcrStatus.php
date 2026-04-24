<?php

namespace App\Enums;

enum OcrStatus: string
{
    case PENDING = 'pending';
    case PROCESSED = 'processed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSED => 'Processed',
            self::FAILED => 'Failed',
        };
    }
}
