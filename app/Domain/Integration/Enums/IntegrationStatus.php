<?php

namespace Integration\Enums;

enum IntegrationStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case NotFound = 'not_found';
    case Failed = 'failed';
}
