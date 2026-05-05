<?php

namespace App\Domain\Music\Enums;

enum AvailabilityMode: string
{
    case Global = 'global';
    case Markets = 'markets';
    case Unknown = 'unknown';
}
