<?php

use OliverDaviesLtd\BuildConfiguration\Console\Command\BuildConfigurationCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';

$app = new Application();

$app->addCommands([
    new BuildConfigurationCommand(),
]);

$app->run();
