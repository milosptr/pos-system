<?php

namespace Services;

use Carbon\Carbon;

class WorkingDay
{

    public static function getWorkingDay(Carbon $date = null)
    {
        if (!$date) $date = Carbon::now();

        if($date->between("00:00:00", "03:59:59", true)) $date = $date->yesterday();

        return [$date->startOfDay()->addHours(4)->toDateTimeString(), $date->endOfDay()->addHours(4)->toDateTimeString()];
    }

}
