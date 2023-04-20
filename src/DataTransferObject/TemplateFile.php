<?php

declare(strict_types=1);

namespace OliverDaviesLtd\BuildConfigs\DataTransferObject;

readonly final class TemplateFile
{
    public function __construct(
        public string $data,
        public string $name,
        public string|null $path = null,
    ) {
    }
}
