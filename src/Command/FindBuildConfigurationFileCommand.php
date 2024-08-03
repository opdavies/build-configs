<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Filesystem\Filesystem;

final class FindBuildConfigurationFileCommand
{
    /**
     * Supported build configuration file names.
     */
    private static $configFiles = [
        'build-configs.yaml',
        'build.yaml',
    ];

    public function __construct(private Filesystem $filesystem)
    {
    }

    public function execute(string|null $configFile = null, \Closure $next)
    {
        if ($configFile !== null) {
            if (!$this->filesystem->exists($configFile)) {
                throw new \RuntimeException(sprintf('%s not found', $configFile));
            }

            return $next($configFile);
        }

        // Search for a configuration file in the order of the filenames within
        // the array and continue if one exists.
        foreach (self::$configFiles as $i) {
            if ($this->filesystem->exists($i)) {
                return $next($i);
            }
        }

        throw new \RuntimeException("No configuration file found");
    }
}
