<?php

declare(strict_types=1);

namespace App\Action;

use App\DataTransferObject\TemplateFile;
use Illuminate\Support\Collection;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

final class GenerateConfigurationFiles
{
    public function __construct(
        private Filesystem $filesystem,
        private Environment $twig,
        private string $outputDir,
    ) {
    }

    public function handle(array $filesToGenerateAndConfigurationData, \Closure $next)
    {
        /**
         * @var Collection<int,TemplateFile> $filesToGenerate
         * @var array<string,mixed> $configurationData
         */
        [$configurationData, $filesToGenerate] = $filesToGenerateAndConfigurationData;

        $filesToGenerate->each(function(TemplateFile $templateFile) use ($configurationData): void {
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
        if ($this->filesystem->exists("{$outputDir}/tools/docker/images/php/root/usr/local/bin/docker-entrypoint-php")) {
            $filesystem->chmod("{$outputDir}/tools/docker/images/php/root/usr/local/bin/docker-entrypoint-php", 0755);
        }

        if ($this->filesystem->exists("{$outputDir}/.githooks/pre-push")) {
            $this->filesystem->chmod("{$outputDir}/.githooks/pre-push", 0755);
        }

        return $next([$configurationData, $filesToGenerate]);
    }
}
