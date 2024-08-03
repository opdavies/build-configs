<?php

declare(strict_types=1);

namespace App\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:init',
    description: 'Add a short description for your command',
)]
class InitCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('projectName', InputArgument::REQUIRED, 'The name of the project')
            ->addArgument('language', InputArgument::REQUIRED, 'The language the project uses')
            ->addArgument('type', InputArgument::REQUIRED, 'The type of project')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        [
            'language' => $language,
            'projectName' => $projectName,
            'type' => $type,
        ] = $input->getArguments();

        $projectName = str_replace('.', '-', $projectName);

        // TODO: validate the project type.
        $output = <<<EOF
            name: $projectName
            language: $language
            type: $type
            EOF;

        file_put_contents('build.yaml', $output);

        return Command::SUCCESS;
    }
}
