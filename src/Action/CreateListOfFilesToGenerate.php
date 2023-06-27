<?php

declare(strict_types=1);

namespace App\Action;

use App\DataTransferObject\Config;
use App\DataTransferObject\TemplateFile;
use App\Enum\Language;
use App\Enum\WebServer;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class CreateListOfFilesToGenerate
{
    public function handle(array $configurationDataAndDto, \Closure $next)
    {
        /**
         * @var Config $configurationDataDto,
         * @var array<string,mixed> $configurationData
         */
        [$configurationData, $configurationDataDto] = $configurationDataAndDto;

        $isDocker = static::isDocker($configurationData);
        $isFlake = static::isFlake($configurationData);

        /** @var Collection<int, TemplateFile> */
        $filesToGenerate = collect();

        if ($isDocker) {
            $filesToGenerate->push(new TemplateFile(data: 'common/.dockerignore', name: '.dockerignore'));
            $filesToGenerate->push(new TemplateFile(data: 'common/.hadolint.yaml', name: '.hadolint.yaml'));
            $filesToGenerate->push(new TemplateFile(data: 'env.example', name: '.env.example'));
        }

        if ($isFlake) {
            $filesToGenerate->push(new TemplateFile(data: 'common/flake.nix', name: 'flake.nix'));
        }

        $extraDatabases = Arr::get($configurationData, 'database.extra_databases', []);
        if (count($extraDatabases) > 0) {
            $filesToGenerate[] = new TemplateFile(
                data: 'extra-databases.sql',
                name: 'extra-databases.sql',
                path: 'tools/docker/images/database/root/docker-entrypoint-initdb.d',
            );
        }

        if (false !== Arr::get($configurationData, "justfile", true)) {
            $filesToGenerate[] = new TemplateFile(data: 'justfile', name: 'justfile');
        }

        if (isset($configurationData['dockerCompose']) && $configurationData['dockerCompose'] !== null) {
            $filesToGenerate[] = new TemplateFile(data: 'docker-compose.yaml', name: 'docker-compose.yaml');
        }

        if (static::isPhp(Arr::get($configurationData, 'language'))) {
            if ($isDocker) {
                $filesToGenerate[] = new TemplateFile(data: 'php/Dockerfile', name: 'Dockerfile');
            }

            $filesToGenerate[] = new TemplateFile(data: 'php/phpcs.xml', name: 'phpcs.xml.dist');
            $filesToGenerate[] = new TemplateFile(data: 'php/phpunit.xml', name: 'phpunit.xml.dist');
            $filesToGenerate[] = new TemplateFile(
                data: 'php/docker-entrypoint-php',
                name: 'docker-entrypoint-php',
                path: 'tools/docker/images/php/root/usr/local/bin',
            );
            $filesToGenerate[] = new TemplateFile(
                data: 'php/php.ini',
                name: 'php.ini',
                path: 'tools/docker/images/php/root/usr/local/etc/php',
            );

            if (Arr::has(array: $configurationData, keys: 'php.phpstan')) {
                $filesToGenerate[] = new TemplateFile(data: 'php/phpstan.neon', name: 'phpstan.neon.dist');
            }
        }

        if (static::isNode(Arr::get($configurationData, 'language'))) {
            if ($isDocker) {
                $filesToGenerate[] = new TemplateFile(data: 'node/.yarnrc', name: '.yarnrc');
                $filesToGenerate[] = new TemplateFile(data: 'node/Dockerfile', name: 'Dockerfile');
            }
        }

        if (static::isCaddy(Arr::get($configurationData, 'web.type'))) {
            $filesToGenerate[] = new TemplateFile(
                data: 'web/caddy/Caddyfile',
                name: 'Caddyfile',
                path: 'tools/docker/images/web/root/etc/caddy',
            );
        }

        if (static::isNginx(Arr::get($configurationData, 'web.type'))) {
            $filesToGenerate[] = new TemplateFile(
                data: 'web/nginx/default.conf',
                name: 'default.conf',
                path: 'tools/docker/images/web/root/etc/nginx/conf.d',
            );
        }

        if ('drupal-project' === Arr::get($configurationData, 'type')) {
            // Add a Drupal version of phpunit.xml.dist.
            $filesToGenerate[] = new TemplateFile(data: 'drupal-project/phpunit.xml.dist', name: 'phpunit.xml.dist');
        }

        if (Arr::get($configurationData, 'experimental.createGitHubActionsConfiguration', false) === true) {
            $filesToGenerate[] = new TemplateFile(
                data: 'ci/github-actions/ci.yml',
                name: 'ci.yml',
                path: '.github/workflows',
            );
        }

        if (Arr::get($configurationData, 'experimental.runGitHooksBeforePush', false) === true) {
            $filesToGenerate[] = new TemplateFile(
                data: 'git-hooks/pre-push',
                name: 'pre-push',
                path: '.githooks',
            );
        }

        return $next([$configurationData, $configurationDataDto, $filesToGenerate]);
    }

    private static function isCaddy(?string $webServer): bool
    {
        if (is_null($webServer)) {
            return false;
        }

        return strtoupper($webServer) === WebServer::CADDY->name;
    }

    private static function isDocker(array $configurationData): bool
    {
        // This should return `false` if there is no explicit `dockerfile` key
        // in the build.yaml file. This is currently not the case, I assume
        // because of default values being added.
        // For now, if it's not a Flake, it's Docker.
        return !static::isFlake($configurationData);
    }

    private static function isFlake(array $configurationData): bool
    {
        return Arr::get($configurationData, 'flake') !== null;
    }

    private static function isNginx(?string $webServer): bool
    {
        if (is_null($webServer)) {
            return false;
        }

        return strtoupper($webServer) === WebServer::NGINX->name;
    }

    private static function isNode(?string $language): bool
    {
        if (is_null($language)) {
            return false;
        }

        return strtoupper($language) === Language::NODE->name;
    }

    private static function isPhp(?string $language): bool
    {
        if (is_null($language)) {
            return false;
        }

        return strtoupper($language) === Language::PHP->name;
    }
}
