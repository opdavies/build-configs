<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectType: string
{
    case Astro = 'astro';
    case Drupal = 'drupal';
    case Fractal = 'fractal';
    case Laravel = 'laravel';
    case PHPLibrary = 'php-library';
    case Symfony = 'symfony';
    case Terraform = 'terraform';
}
