<?php

declare(strict_types=1);

namespace OliverDaviesLtd\BuildConfigs\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

#[AsCommand(
    description: 'Build configuration files',
    name: 'build-configs'
)]
final class BuildConfigurationCommand extends Command
{
    private const LANGUAGE_PHP = 'php';

    public function __construct(
        private Environment $twig,
        private Filesystem $filesystem,
    ) {
        parent::__construct();
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
        $outputDir = $input->getOption('output-dir');

        $io = new SymfonyStyle($input, $output);

        $configurationData = Yaml::parseFile($configFile);
        $configurationData['dockerCompose'] = $configurationData['docker-compose'];
        $configurationData['docker-compose'] = null;

        $io->info("Building configuration for {$configurationData['name']}.");

        $this->filesystem->dumpFile("{$outputDir}/.env.example", $this->twig->render('env.example.twig', $configurationData));
        $this->filesystem->dumpFile("{$outputDir}/Dockerfile", $this->twig->render('Dockerfile.twig', $configurationData));

        if ($configurationData['dockerCompose'] !== null) {
            $this->filesystem->dumpFile("{$outputDir}/docker-compose.yaml", $this->twig->render('docker-compose.yaml.twig', $configurationData));
        }

        if ($configurationData['language'] === self::LANGUAGE_PHP) {
            $this->filesystem->dumpFile("{$outputDir}/phpcs.xml.dist", $this->twig->render('php/phpcs.xml.twig', $configurationData));
            $this->filesystem->dumpFile("{$outputDir}/phpstan.neon.dist", $this->twig->render('php/phpstan.neon.twig', $configurationData));
            $this->filesystem->dumpFile("{$outputDir}/phpunit.xml.dist", $this->twig->render('php/phpunit.xml.twig', $configurationData));

            $this->filesystem->mkdir("{$outputDir}/tools/docker/images/php/root/usr/local/bin");
            $this->filesystem->dumpFile("{$outputDir}/tools/docker/images/php/root/usr/local/bin/docker-entrypoint-php", $this->twig->render('php/docker-entrypoint-php.twig', $configurationData));
        }

        if ($configurationData['web']['type'] === 'nginx') {
            $this->filesystem->mkdir("{$outputDir}/tools/docker/images/nginx/root/etc/nginx/conf.d");
            $this->filesystem->dumpFile("{$outputDir}/tools/docker/images/nginx/root/etc/nginx/conf.d/default.conf", $this->twig->render('default.conf', $configurationData));
        }

        return Command::SUCCESS;
    }
}
