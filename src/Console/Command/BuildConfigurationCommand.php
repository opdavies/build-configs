<?php

declare(strict_types=1);

namespace OliverDaviesLtd\BuildConfiguration\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

#[AsCommand(
    description: 'Build configuration files',
    name: 'build-configuration'
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

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $configurationData = Yaml::parseFile(getcwd().'/build.yaml');
        $configurationData['dockerCompose'] = $configurationData['docker-compose'];
        $configurationData['docker-compose'] = null;

        $io->info("Building configuration for {$configurationData['name']}.");

        $this->filesystem->dumpFile('.env.example', $this->twig->render('env.example.twig', $configurationData));
        $this->filesystem->dumpFile('Dockerfile', $this->twig->render('Dockerfile.twig', $configurationData));

        if ($configurationData['dockerCompose'] === true) {
            $this->filesystem->dumpFile('docker-compose.yaml', $this->twig->render('docker-compose.yaml.twig', $configurationData));
        }

        if ($configurationData['language'] === self::LANGUAGE_PHP) {
            $this->filesystem->dumpFile('phpcs.xml.dist', $this->twig->render('phpcs.xml.twig', $configurationData));
            $this->filesystem->dumpFile('phpstan.neon.dist', $this->twig->render('phpstan.neon.twig', $configurationData));
            $this->filesystem->dumpFile('phpunit.xml.dist', $this->twig->render('phpunit.xml.twig', $configurationData));
        }

        return Command::SUCCESS;
    }
}

