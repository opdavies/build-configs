<?php

declare(strict_types=1);

namespace App\Action;

use App\DataTransferObject\Config;
use App\DataTransferObject\TemplateFile;
use App\Enum\Language;
use App\Enum\ProjectType;
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

        switch (strtolower($configurationDataDto->type)) {
            case (strtolower(ProjectType::Astro->name)):
                $filesToGenerate = collect([
                    new TemplateFile(data: 'astro/.envrc', name: '.envrc'),
                    new TemplateFile(data: 'astro/.gitignore', name: '.gitignore'),
                    new TemplateFile(data: 'astro/flake.nix', name: 'flake.nix'),
                    new TemplateFile(data: 'astro/justfile', name: 'justfile'),
                    new TemplateFile(data: 'astro/tsconfig.json', name: 'tsconfig.json'),
                ]);
                break;

            case (strtolower(ProjectType::Fractal->name)):
                $filesToGenerate = collect([
                    new TemplateFile(data: 'fractal/.gitignore', name: '.gitignore'),
                    new TemplateFile(data: 'fractal/justfile', name: 'justfile'),
                ]);

                if (self::isDocker($configurationData)) {
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/.env.example', name: '.env.example'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/.dockerignore', name: '.dockerignore'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/.hadolint.yaml', name: '.hadolint.yaml'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/.yarnrc', name: '.yarnrc'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/Dockerfile', name: 'Dockerfile'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/docker-compose.yaml', name: 'docker-compose.yaml'));
                } elseif (self::isFlake($configurationData)) {
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/.envrc', name: '.envrc'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/flake.nix', name: 'flake.nix'));
                }
                break;

            case (strtolower(ProjectType::Drupal->name)):
                $filesToGenerate = collect([
                    new TemplateFile(data: 'drupal/.dockerignore', name: '.dockerignore'),
                    new TemplateFile(data: 'drupal/.env.example', name: '.env.example'),
                    new TemplateFile(data: 'drupal/.gitignore', name: '.gitignore'),
                    new TemplateFile(data: 'drupal/.hadolint.yaml', name: '.hadolint.yaml'),
                    new TemplateFile(data: 'drupal/Dockerfile', name: 'Dockerfile'),
                    new TemplateFile(data: 'drupal/docker-compose.yaml', name: 'docker-compose.yaml'),
                    new TemplateFile(data: 'drupal/justfile', name: 'justfile'),
                ]);

                $extraDatabases = Arr::get($configurationData, 'database.extra_databases', []);
                if (count($extraDatabases) > 0) {
                    $filesToGenerate->push(new TemplateFile(
                        data: 'drupal/extra-databases.sql',
                        name: 'extra-databases.sql',
                        path: 'tools/docker/images/database/root/docker-entrypoint-initdb.d',
                    ));
                }

                $filesToGenerate->push(new TemplateFile(data: 'drupal/phpcs.xml.dist', name: 'phpcs.xml.dist'));
                $filesToGenerate->push(new TemplateFile(data: 'drupal/phpunit.xml.dist', name: 'phpunit.xml.dist'));
                $filesToGenerate->push(new TemplateFile(
                    data: 'drupal/docker-entrypoint-php',
                    name: 'docker-entrypoint-php',
                    path: 'tools/docker/images/php/root/usr/local/bin',
                ));

                $filesToGenerate->push(new TemplateFile(
                    data: 'drupal/php.ini',
                    name: 'php.ini',
                    path: 'tools/docker/images/php/root/usr/local/etc/php',
                ));
                break;
        }

        if ($filesToGenerate->isNotEmpty()) {
            return $next([$configurationData, $configurationDataDto, $filesToGenerate]);
        }

        if ($isDocker) {
            $filesToGenerate->push(new TemplateFile(data: 'common/.dockerignore', name: '.dockerignore'));
            $filesToGenerate->push(new TemplateFile(data: 'common/.hadolint.yaml', name: '.hadolint.yaml'));
            $filesToGenerate->push(new TemplateFile(data: 'env.example', name: '.env.example'));
        }

        if ($isFlake) {
            $filesToGenerate->push(new TemplateFile(data: 'common/envrc', name: '.envrc'));
            $filesToGenerate->push(new TemplateFile(data: 'common/flake.nix', name: 'flake.nix'));
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

        if (Arr::get($configurationData, 'experimental.createGitHubActionsConfiguration', false) === true) {
            $filesToGenerate[] = new TemplateFile(
                data: 'ci/github-actions/ci.yml',
                name: 'ci.yml',
                path: '.github/workflows',
            );
        }

        $filesToGenerate[] = new TemplateFile(
            data: 'git-hooks/prepare-commit-msg',
            name: 'prepare-commit-msg',
            path: '.githooks',
        );

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

    private static function isTypeScript(?string $language): bool
    {
        if (is_null($language)) {
            return false;
        }

        return strtoupper($language) === Language::TYPESCRIPT->name;
    }

    private static function isPhp(?string $language): bool
    {
        if (is_null($language)) {
            return false;
        }

        return strtoupper($language) === Language::PHP->name;
    }
}
