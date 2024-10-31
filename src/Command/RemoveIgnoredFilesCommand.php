<?php

declare(strict_types=1);

namespace App\Command;

use App\DataTransferObject\ConfigDto;
use App\DataTransferObject\TemplateFile;
use Illuminate\Support\Collection;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

final class RemoveIgnoredFilesCommand
{
    public function __construct(private array $filenames)
    {
    }

    public function execute(array $filesToGenerateAndConfigurationData, \Closure $next)
    {
        /**
         * @var Collection<int,TemplateFile> $filesToGenerate
         * @var ConfigDto $configurationDataDto,
         * @var array<non-empty-string,mixed> $configurationData
         */
        [$configurationData, $configurationDataDto, $filesToGenerate] = $filesToGenerateAndConfigurationData;

        $filesToGenerate = $filesToGenerate->filter(function (TemplateFile $templateFile): bool {
            return !collect($this->filenames)->contains($templateFile->name);
        });

        return $next([$configurationDataDto, $filesToGenerate]);
    }
}
