<?php

declare(strict_types=1);

namespace App\Action;

use App\DataTransferObject\ConfigDto;
use App\DataTransferObject\TemplateFile;
use App\Enum\ProjectType;
use App\Enum\WebServer;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class CreateListOfFilesToGenerate
{
    public function handle(array $configurationDataAndDto, \Closure $next)
    {
        /**
         * @var ConfigDto $configDto,
         * @var array<string,mixed> $configurationData
         */
        [$configurationData, $configDto] = $configurationDataAndDto;

        /** @var Collection<int, TemplateFile> */
        $filesToGenerate = collect();

        switch (strtolower($configDto->type)) {
            case (strtolower(ProjectType::Sculpin->name)):
                $filesToGenerate = collect([
                    new TemplateFile(data: 'php/sculpin/.gitignore', name: '.gitignore'),
                    new TemplateFile(data: 'php/sculpin/run', name: 'run'),
                ]);

                if ($configDto->isFlake) {
                    $filesToGenerate->push(new TemplateFile(data: 'php/common/flake.nix', name: 'flake.nix'));
                }
                break;

            case (strtolower(ProjectType::Symfony->name)):
                if ($configDto->isFlake) {
                    $filesToGenerate->push(new TemplateFile(data: 'php/common/flake.nix', name: 'flake.nix'));
                }
                break;

            case (strtolower(ProjectType::Fractal->name)):
                $filesToGenerate = collect([
                    new TemplateFile(data: 'fractal/.gitignore', name: '.gitignore'),
                    new TemplateFile(data: 'fractal/run', name: 'run'),
                ]);

                if ($configDto->isDocker) {
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/.env.example', name: '.env.example'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/.dockerignore', name: '.dockerignore'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/.hadolint.yaml', name: '.hadolint.yaml'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/.yarnrc', name: '.yarnrc'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/Dockerfile', name: 'Dockerfile'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/docker-compose.yaml', name: 'docker-compose.yaml'));
                } elseif ($configDto->isFlake) {
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/.envrc', name: '.envrc'));
                    $filesToGenerate->push(new TemplateFile(data: 'fractal/flake.nix', name: 'flake.nix'));
                }

                if (Arr::get($configurationData, 'experimental.createGitHubActionsConfiguration', false) === true) {
                    $filesToGenerate[] = new TemplateFile(
                        data: 'fractal/.github/workflows/ci.yml',
                        name: 'ci.yml',
                        path: '.github/workflows',
                    );
                }
                break;

            case (strtolower(ProjectType::Drupal->name)):
                $filesToGenerate = collect([
                    new TemplateFile(data: 'php/drupal/.dockerignore', name: '.dockerignore'),
                    new TemplateFile(data: 'php/drupal/.env.example', name: '.env.example'),
                    new TemplateFile(data: 'php/drupal/.gitignore', name: '.gitignore'),
                    new TemplateFile(data: 'php/drupal/.hadolint.yaml', name: '.hadolint.yaml'),
                    new TemplateFile(data: 'php/drupal/Dockerfile', name: 'Dockerfile'),
                    new TemplateFile(data: 'php/drupal/docker-compose.yaml', name: 'docker-compose.yaml'),
                    new TemplateFile(data: 'php/drupal/run', name: 'run'),
                ]);

                $extraDatabases = Arr::get($configurationData, 'database.extra_databases', []);
                if (count($extraDatabases) > 0) {
                    $filesToGenerate->push(new TemplateFile(
                        data: 'php/drupal/extra-databases.sql',
                        name: 'extra-databases.sql',
                        path: 'tools/docker/images/database/root/docker-entrypoint-initdb.d',
                    ));
                }

                if (!isset($configDto->php['phpcs']) || $configDto->php['phpcs'] !== false) {
                    $filesToGenerate->push(new TemplateFile(data: 'php/drupal/phpcs.xml.dist', name: 'phpcs.xml.dist'));
                }

                if (!isset($configDto->php['phpstan']) || $configDto->php['phpstan'] !== false) {
                    $filesToGenerate->push(new TemplateFile(data: 'php/drupal/phpstan.neon.dist', name: 'phpstan.neon.dist'));
                }

                if (!isset($configDto->php['phpunit']) || $configDto->php['phpunit'] !== false) {
                    $filesToGenerate->push(new TemplateFile(data: 'php/drupal/phpunit.xml.dist', name: 'phpunit.xml.dist'));
                }

                $filesToGenerate->push(new TemplateFile(
                    data: 'php/drupal/docker-entrypoint-php',
                    name: 'docker-entrypoint-php',
                    path: 'tools/docker/images/php/root/usr/local/bin',
                ));

                $filesToGenerate->push(new TemplateFile(
                    data: 'php/drupal/php.ini',
                    name: 'php.ini',
                    path: 'tools/docker/images/php/root/usr/local/etc/php',
                ));

                if (static::isCaddy(Arr::get($configurationData, 'web.type'))) {
                    $filesToGenerate[] = new TemplateFile(
                        data: 'php/drupal/caddy/Caddyfile',
                        name: 'Caddyfile',
                        path: 'tools/docker/images/web/root/etc/caddy',
                    );
                }

                if (static::isNginx(Arr::get($configurationData, 'web.type'))) {
                    $filesToGenerate[] = new TemplateFile(
                        data: 'php/drupal/nginx/default.conf',
                        name: 'default.conf',
                        path: 'tools/docker/images/web/root/etc/nginx/conf.d',
                    );
                }

                if (Arr::get($configurationData, 'experimental.createGitHubActionsConfiguration', false) === true) {
                    $filesToGenerate[] = new TemplateFile(
                        data: 'php/drupal/.github/workflows/ci.yml',
                        name: 'ci.yml',
                        path: '.github/workflows',
                    );
                }
                break;

            case (strtolower(ProjectType::Terraform->name)):
                $filesToGenerate = collect([
                    new TemplateFile(data: 'terraform/.gitignore', name: '.gitignore'),
                    new TemplateFile(data: 'terraform/run', name: 'run'),
                ]);
                break;
        }

        if (Arr::get($configurationData, 'experimental.createTmuxStartupFile') === true) {
            $filesToGenerate[] = new TemplateFile(
                data: 'common/.tmux',
                name: '.tmux',
            );
        }

        $filesToGenerate[] = new TemplateFile(
            data: 'common/.githooks/prepare-commit-msg',
            name: 'prepare-commit-msg',
            path: '.githooks',
        );

        if (Arr::get($configurationData, 'experimental.runGitHooksBeforePush', false) === true) {
            $filesToGenerate[] = new TemplateFile(
                data: 'common/.githooks/pre-push',
                name: 'pre-push',
                path: '.githooks',
            );
        }

        return $next([$configurationData, $configDto, $filesToGenerate]);
    }

    private static function isCaddy(?string $webServer): bool
    {
        if (is_null($webServer)) {
            return false;
        }

        return $webServer === WebServer::Caddy->value;
    }

    private static function isNginx(?string $webServer): bool
    {
        if (is_null($webServer)) {
            return false;
        }

        return $webServer === WebServer::Nginx->value;
    }
}
