<?php

declare(strict_types=1);

namespace App\Enum;

enum WebServer: string
{
    case Apache = 'apache';
    case Caddy = 'caddy';
    case Nginx = 'nginx';
}
