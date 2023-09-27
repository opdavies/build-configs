<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectType
{
    case Astro;
    case Drupal;
    case Fractal;
    case Terraform;
}
