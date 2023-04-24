<?php

declare(strict_types=1);

namespace App\Command;

use App\Action\CreateFinalConfigurationData;
use App\Action\CreateListOfFilesToGenerate;
use App\Action\GenerateConfigurationFiles;
use App\Action\ValidateConfigurationData;
use App\DataTransferObject\TemplateFile;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

#[AsCommand(
    name: 'app:generate',
    description: 'Generate project-specific configuration files',
)]
class GenerateCommand extends Command
{
    public function __construct(
        private Filesystem $filesystem,
        private Environment $twig,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                name: 'config-file',
                shortcut: ['c'],
                mode: InputOption::VALUE_REQUIRED,
                description: 'The path to the project\'s build.yaml file',
                default: 'build.yaml',
            )
            ->addOption(
                name: 'output-dir',
                shortcut: ['o'],
                mode: InputOption::VALUE_REQUIRED,
                description: 'The directory to create files in',
                default: '.',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $configFile = $input->getOption(name: 'config-file');
        $outputDir = $input->getOption(name: 'output-dir');

        $pipelines = [
            new CreateFinalConfigurationData(),

            new ValidateConfigurationData(),

            new CreateListOfFilesToGenerate(),

            new GenerateConfigurationFiles(
                $this->filesystem,
                $this->twig,
                $outputDir,
            ),
        ];

        /**
         * @var Collection<int,TemplateFile> $generatedFiles
         * @var array<string,mixed> $configurationData
         */
        [$configurationData, $generatedFiles] = (new Pipeline())
            ->send($configFile)
            ->through($pipelines)
            ->thenReturn();

        $io->info("Building configuration for {$configurationData['name']}.");

        $io->write('Generated files:');
        $io->listing(static::getListOfFiles(filesToGenerate: $generatedFiles)->toArray());

        return Command::SUCCESS;
    }

    private static function buildFilePath(TemplateFile $templateFile): string
    {
        return collect([$templateFile->path, $templateFile->name])->filter()->implode('/');
    }

    private static function getListOfFiles(Collection $filesToGenerate): Collection
    {
        return $filesToGenerate
            ->map(fn (TemplateFile $templateFile): string => static::buildFilePath($templateFile))
            ->unique()
            ->sort();
    }
}
