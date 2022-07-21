<?php

namespace Services;

use Carbon\Carbon;

class WorkingDay
{

    public static function getWorkingDay()
    {
        $now = Carbon::now();
        return [$now->startOfDay()->addHours(4), $now->endOfDay()->addHours(4)];
    }

    public static function getWorkingDayForDate(Carbon $dateTime)
    {
        return [$dateTime->startOfDay()->addHours(4), $dateTime->endOfDay()->addHours(4)];
    }
}
