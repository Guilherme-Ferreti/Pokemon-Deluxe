<?php

declare(strict_types=1);

namespace Shared\Enums;

enum Generation: int
{
    case KANTO  = 1;
    case JOHTO  = 2;
    case HOENN  = 3;
    case SINNOH = 4;
    case UNOVA  = 5;
    case KALOS  = 6;
    case ALOLA  = 7;
    case GALAR  = 8;
    case PALDEA = 9;
}
