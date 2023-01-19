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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $buildYaml = Yaml::parseFile(getcwd().'/build.yaml');

        $io->info("Building configuration for {$buildYaml['name']}.");

        if ($buildYaml['language'] === self::LANGUAGE_PHP) {
            $this->filesystem->dumpFile('Dockerfile', $this->twig->render('php/Dockerfile.twig', $buildYaml));
        }

        return Command::SUCCESS;
    }
}

