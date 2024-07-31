<?php

declare(strict_types=1);

namespace App\Action;

use Illuminate\Support\Arr;
use Symfony\Component\Yaml\Yaml;

final class CreateFinalConfigurationData
{
    public function handle(string $configFile, \Closure $next)
    {
        // Perform some initial checks before the defaults are merged.
        $configurationData = Yaml::parseFile(filename: $configFile);

        $configurationData = array_replace_recursive(
            Yaml::parseFile(filename: __DIR__ . '/../../resources/build.defaults.yaml'),
            $configurationData,
        );

        // Map the new `template` value to `type`.
        if (isset($configurationData['template'])) {
            $configurationData['type'] = match ($configurationData['template']) {
                'sculpin-site' => 'sculpin',
                default => $configurationData['template'],
            };

            $configurationData['template'] = null;
        }

        // Flatten the new `parameters` into the main configuration.
        if (isset($configurationData['parameters'])) {
            $configurationData = array_merge($configurationData, [...$configurationData['parameters']]);
        }

        // `flake` renamed to `nix`.
        if (isset($configurationData['nix'])) {
            $configurationData['flake'] = $configurationData['nix'];
            $configurationData['nix'] = null;
        }

        $configurationData['isDocker'] = isset($configurationData['dockerfile']);
        $configurationData['isFlake'] = isset($configurationData['flake']);

        if (isset($configurationData['docker-compose'])) {
            $configurationData['dockerCompose'] = $configurationData['docker-compose'];
            $configurationData['docker-compose'] = null;
        }

        $configurationData['managedText'] = 'Do not edit this file. It is automatically generated by https://www.oliverdavies.uk/build-configs.';

        $basePackages = [
            'git',
            'libpng-dev',
            'libjpeg-dev',
            'libzip-dev',
            // TODO: only add `mariadb-client` if MariaDB is used.
            'mariadb-client',
            'unzip',
        ];

        $phpExtensions = [
            'gd',
            'opcache',
            // TODO: only add `pdo_mysql` if its used.
            'pdo_mysql',
            'zip',
        ];

        $configurationData['dockerfile']['stages']['build']['packages'] = $basePackages;

        $configurationData['dockerfile']['stages']['build']['extensions']['install'] = collect($phpExtensions)
            ->merge(Arr::get($configurationData, 'dockerfile.stages.build.extensions.install'))
            ->unique()
            ->sort()
            ->toArray();

        return $next($configurationData);
    }
}
