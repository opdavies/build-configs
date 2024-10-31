<?php

declare(strict_types=1);

namespace App;

final class IgnoreFile
{
    public const FILENAME = '.bcignore';

    public static function parse(): array
    {
        return explode("\n", file_get_contents(self::FILENAME));
    }
}
