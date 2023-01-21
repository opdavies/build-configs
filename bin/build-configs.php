<?php

use OliverDaviesLtd\BuildConfiguration\Console\Command\BuildConfigurationCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/../vendor/autoload.php';

$filesystem = new Filesystem();
$twig = new Environment(new FilesystemLoader([__DIR__.'/../templates']));

$application = new Application();

$application->addCommands([
    new BuildConfigurationCommand($twig, $filesystem),
]);

$application->setDefaultCommand('build-configuration', true);
$application->run();
