<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectType: string
{
    case Drupal = 'drupal';
    case Fractal = 'fractal';
    case Laravel = 'laravel';
    case PHPLibrary = 'php-library';
    case Sculpin = 'sculpin';
    case Symfony = 'symfony';
}
