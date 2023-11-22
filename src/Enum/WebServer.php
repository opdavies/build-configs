<?php

declare(strict_types=1);

namespace App\Enum;

enum WebServer: string
{
    case Caddy = 'caddy';
    case Nginx = 'nginx';
}
