<?php
namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum OcrStatus: string implements HasLabel, HasColor, HasIcon {
    case PENDING   = 'pending';
    case PROCESSED = 'processed';
    case FAILED    = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING   => 'Pending',
            self::PROCESSED => 'Processed',
            self::FAILED    => 'Failed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING   => 'primary',
            self::PROCESSED => 'success',
            self::FAILED    => 'danger',
        };
    }

    public function getIcon(): string | BackedEnum | Htmlable | null
    {
        return match ($this) {
            self::PENDING   => Heroicon::Clock,
            self::PROCESSED => Heroicon::CheckCircle,
            self::FAILED    => Heroicon::XCircle,
        };
    }
}
