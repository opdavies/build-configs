<?php

declare(strict_types=1);

namespace OliverDaviesLtd\BuildConfiguration\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

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

        // Parse its contents.
        // Generate the appropriate configuration files.
    //
        return Command::SUCCESS;
    }
}

