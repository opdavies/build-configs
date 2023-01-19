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
use Twig\Loader\FilesystemLoader;

#[AsCommand(
    description: 'Build configuration files',
    name: 'build-configuration'
)]
final class BuildConfigurationCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Find a build.yaml file.
        $buildYaml = Yaml::parseFile(getcwd().'/build.yaml');

        $io = new SymfonyStyle($input, $output);
        $io->info("Building configuration for {$buildYaml['name']}.");

        $twig = new Environment(new FilesystemLoader([__DIR__.'/../../../templates']));
        $output = $twig->render('test.twig', $buildYaml);

        $fs = new Filesystem();
        $fs->dumpFile('test.txt', $output);
    //
        return Command::SUCCESS;
    }
}

