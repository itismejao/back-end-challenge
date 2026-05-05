<?php

namespace App\Domain\Music\Enums;

enum ReleaseDatePrecision: string
{
    case Day = 'day';
    case Month = 'month';
    case Year = 'year';
}
