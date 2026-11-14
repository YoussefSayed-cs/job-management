<?php

namespace App\Enums;

enum WorkLocationType: string
{
    case OnSite = 'on_site';
    case Remote = 'remote';
    case Hybrid = 'hybrid';
}
