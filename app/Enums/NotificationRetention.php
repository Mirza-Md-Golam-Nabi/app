<?php

namespace App\Enums;

enum NotificationRetention: string
{
    case OneDay = 'one_day';

    case OneWeek = 'one_week';

    case OneMonth = 'one_month';

    case ThreeMonths = 'three_months';

    case SixMonths = 'six_months';

    case OneYear = 'one_year';

    case TwoYears = 'two_years';

    case Forever = 'forever';
}
