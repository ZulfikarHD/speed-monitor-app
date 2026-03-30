<?php

namespace App\Enums;

enum TripStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case AutoStopped = 'auto_stopped';
}
