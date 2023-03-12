<?php

declare(strict_types=1);

namespace OliverDaviesLtd\BuildConfigs\Console\Command;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use OliverDaviesLtd\BuildConfigs\Enum\Language;
use OliverDaviesLtd\BuildConfigs\Enum\WebServer;
use OliverDaviesLtd\BuildConfigs\Validator\ConfigurationValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

#[AsCommand(
    description: 'Build configuration files',
    name: 'build-configs'
)]
final class BuildConfigurationCommand extends Command
{
    /** @phpstan-ignore-next-line */
    private Collection $filesToGenerate;

    private string $outputDir;

    public function __construct(
        private Environment $twig,
        private Filesystem $filesystem,
        private ConfigurationValidator $configurationValidator,
    ) {
        parent::__construct();

        $this->filesToGenerate = new Collection();
    }

    protected function configure(): void
    {
        $this
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'The configuration file to use', 'build.yaml')
            ->addOption('output-dir', 'o', InputOption::VALUE_REQUIRED, 'The directory to create files in', '.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $configFile = $input->getOption('config');
        $this->outputDir = $input->getOption('output-dir');

        $io = new SymfonyStyle($input, $output);

        $configurationData = array_merge(
            Yaml::parseFile(__DIR__.'/../../../resources/build.defaults.yaml'),
            Yaml::parseFile($configFile),
        );

        $violations = $this->configurationValidator->validate($configurationData);

        if (0 < $violations->count()) {
            $io->error('Configuration is invalid.');

            $io->listing(
                collect($violations)
                    ->map(fn (ConstraintViolationInterface $v) => "{$v->getInvalidValue()} - {$v->getMessage()}")
                    ->toArray()
            );

            return Command::FAILURE;
        }

        if (isset($configurationData['docker-compose'])) {
            $configurationData['dockerCompose'] = $configurationData['docker-compose'];
            $configurationData['docker-compose'] = null;
        }

        $io->info("Building configuration for {$configurationData['name']}.");

        $this->filesToGenerate->push(['env.example', '.env.example']);

        if (false !== Arr::get($configurationData, "justfile", true)) {
            $this->filesToGenerate->push(['justfile', 'justfile']);
        }

        if (isset($configurationData['dockerCompose']) && $configurationData['dockerCompose'] !== null) {
            $this->filesToGenerate->push(['docker-compose.yaml', 'docker-compose.yaml']);
        }

        if (self::isPhp(Arr::get($configurationData, 'language'))) {
            $this->filesToGenerate->push(['php/Dockerfile', 'Dockerfile']);
            $this->filesToGenerate->push(['php/phpcs.xml', 'phpcs.xml.dist']);
            $this->filesToGenerate->push(['php/phpstan.neon', 'phpstan.neon.dist']);
            $this->filesToGenerate->push(['php/phpunit.xml', 'phpunit.xml.dist']);

            $this->filesystem->mkdir("{$this->outputDir}/tools/docker/images/php/root/usr/local/bin");
            $this->filesToGenerate->push(['php/docker-entrypoint-php', 'tools/docker/images/php/root/usr/local/bin/docker-entrypoint-php']);
        }

        if (self::isNode(Arr::get($configurationData, 'language'))) {
            $this->filesToGenerate->push(['node/.yarnrc', '.yarnrc']);
            $this->filesToGenerate->push(['node/Dockerfile', 'Dockerfile']);
        }

        if (self::isCaddy(Arr::get($configurationData, 'web.type'))) {
            $this->filesystem->mkdir("{$this->outputDir}/tools/docker/images/web/root/etc/caddy");
            $this->filesToGenerate->push(['web/caddy/Caddyfile', 'tools/docker/images/web/root/etc/caddy/Caddyfile']);
        }

        if (self::isNginx(Arr::get($configurationData, 'web.type'))) {
            $this->filesystem->mkdir("{$this->outputDir}/tools/docker/images/web/root/etc/nginx/conf.d");
            $this->filesToGenerate->push(['web/nginx/default.conf', 'tools/docker/images/web/root/etc/nginx/conf.d/default.conf']);
        }

        if ('drupal-project' === Arr::get($configurationData, 'type')) {
            // Ensure a "docroot" value is set.
            if (null === Arr::get($configurationData, 'drupal.docroot')) {
                Arr::set($configurationData, 'drupal.docroot', 'web');
            }

            // Add a Drupal version of phpunit.xml.dist.
            $this->filesToGenerate->push(['drupal-project/phpunit.xml.dist', 'phpunit.xml.dist']);
        }

        $configurationData['managedText'] = 'Do not edit this file. It is automatically generated by \'build-configs\'.';

        $this->generateFiles($configurationData);

        return Command::SUCCESS;
    }

    /**
     * @param array<string, string> $configurationData
     */
    private function generateFiles(array $configurationData): void
    {
        $this->filesToGenerate->map(function(array $filenames): array {
            $filenames[0] = "{$filenames[0]}.twig";
            $filenames[1] = "{$this->outputDir}/${filenames[1]}";

            return $filenames;
        })->each(function(array $filenames) use ($configurationData): void {
            $this->filesystem->dumpFile($filenames[1], $this->twig->render($filenames[0], $configurationData));
        });

        // If the Docker entrypoint file is generated, ensure it is executable.
        if ($this->filesystem->exists("{$this->outputDir}/tools/docker/images/php/root/usr/local/bin/docker-entrypoint-php")) {
            $this->filesystem->chmod("{$this->outputDir}/tools/docker/images/php/root/usr/local/bin/docker-entrypoint-php", 0755);
        }
    }

    private static function isCaddy(?string $webServer): bool
    {
        if (is_null($webServer)) {
            return false;
        }

        return strtoupper($webServer) === WebServer::CADDY->name;
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
