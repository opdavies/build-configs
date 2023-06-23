<?php

declare(strict_types=1);

namespace App\Action;

use Symfony\Component\Yaml\Yaml;

final class CreateFinalConfigurationData
{
    public function handle(string $configFile, \Closure $next) {
        $configurationData = array_merge(
            Yaml::parseFile(filename: __DIR__ . '/../../resources/build.defaults.yaml'),
            Yaml::parseFile(filename: $configFile),
        );

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
            // TODO: only add `pdo_mysql` if its used.
            'pdo_mysql',
            'zip',
        ];

        $configurationData['dockerfile']['stages']['build']['packages'] = $basePackages;

        $configurationData['dockerfile']['stages']['build']['extensions']['install'] = collect($phpExtensions)
            ->merge($configurationData['dockerfile']['stages']['build']['extensions']['install'])
            ->unique()
            ->sort()
            ->toArray();

        return $next($configurationData);
    }
}
