<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function formatDate(?string $value, string $format = 'd M Y'): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->format($format);
    }
}
