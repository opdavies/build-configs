<?php

declare(strict_types=1);

namespace App\Command;

use App\DataTransferObject\ConfigDto;
use App\DataTransferObject\TemplateFile;
use Illuminate\Support\Collection;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

final class GenerateConfigurationFilesCommand
{
    public function __construct(
        private Filesystem $filesystem,
        private Environment $twig,
        private string $outputDir,
        private bool $isDryRun = false,
    ) {
    }

    public function execute(array $filesToGenerateAndConfigurationData, \Closure $next)
    {
        /**
         * @var Collection<int,TemplateFile> $filesToGenerate
         * @var ConfigDto $configurationDataDto,
         * @var array<non-empty-string,mixed> $configurationData
         */
        [$configurationData, $configurationDataDto, $filesToGenerate] = $filesToGenerateAndConfigurationData;

        if ($this->isDryRun) {
            return $next([$configurationDataDto, $filesToGenerate]);
        }

        $filesToGenerate->each(function (TemplateFile $templateFile) use ($configurationData): void {
            if ($templateFile->path !== null) {
                if (!$this->filesystem->exists($templateFile->path)) {
                    $this->filesystem->mkdir("{$this->outputDir}/{$templateFile->path}");
                }
            }

            $sourceFile = "{$templateFile->data}.twig";

            $outputFile = collect([
                $this->outputDir,
                $templateFile->path,
                $templateFile->name,
            ])->filter()->implode('/');

            $this->filesystem->dumpFile($outputFile, $this->twig->render($sourceFile, $configurationData));
        });

        // If the Docker entrypoint file is generated, ensure it is executable.
        if ($this->filesystem->exists("{$this->outputDir}/tools/docker/images/php/root/usr/local/bin/docker-entrypoint-php")) {
            $this->filesystem->chmod("{$this->outputDir}/tools/docker/images/php/root/usr/local/bin/docker-entrypoint-php", 0755);
        }

        if ($this->filesystem->exists("{$this->outputDir}/.githooks/pre-push")) {
            $this->filesystem->chmod("{$this->outputDir}/.githooks/pre-push", 0755);
        }

        if ($this->filesystem->exists("{$this->outputDir}/.githooks/prepare-commit-msg")) {
            $this->filesystem->chmod("{$this->outputDir}/.githooks/prepare-commit-msg", 0755);
        }

        if ($this->filesystem->exists("{$this->outputDir}/.tmux")) {
            $this->filesystem->chmod("{$this->outputDir}/.tmux", 0755);
        }

        if ($this->filesystem->exists("{$this->outputDir}/run")) {
            $this->filesystem->chmod("{$this->outputDir}/run", 0755);
        }

        return $next([$configurationDataDto, $filesToGenerate]);
    }
}
