<?php

declare(strict_types=1);

namespace App\DataTransferObject;

final class TemplateFile
{
    public function __construct(
        readonly public string $data,
        readonly public string $name,
        readonly public string|null $path = null,
    ) {
    }
}
