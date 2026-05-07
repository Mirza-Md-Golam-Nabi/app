<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case Success = 'success';

    case Failed = 'failed';

    case Info = 'info';

    case Warning = 'warning';
}
