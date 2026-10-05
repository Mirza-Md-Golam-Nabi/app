<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * How a school's student count reached the central app.
 */
enum SchoolReportSource: string implements HasColor, HasLabel
{
    /** The school sent it on its own (its monthly schedule, or a manual run). */
    case Push = 'push';

    /** The central app asked the school for it. */
    case Pull = 'pull';

    public function getLabel(): string
    {
        return match ($this) {
            self::Push => 'Push (স্কুল পাঠিয়েছে)',
            self::Pull => 'Pull (সেন্ট্রাল এনেছে)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Push => 'success',
            self::Pull => 'info',
        };
    }
}
