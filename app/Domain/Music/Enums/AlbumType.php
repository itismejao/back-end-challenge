<?php

namespace App\Domain\Music\Enums;

enum AlbumType: string
{
    case Album = 'album';
    case Single = 'single';
    case Compilation = 'compilation';
    case Ep = 'ep';
}
