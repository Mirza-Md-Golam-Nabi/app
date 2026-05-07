<?php

namespace App\Enums;

enum NotificationRetention: string
{
    case OneDay = 'OneDay';

    case OneWeek = 'OneWeek';

    case OneMonth = 'OneMonth';

    case ThreeMonths = 'ThreeMonths';

    case SixMonths = 'SixMonths';

    case OneYear = 'OneYear';

    case TwoYears = 'TwoYears';

    case Forever = 'Forever';
}
