<?php

declare(strict_types=1);

namespace OliverDaviesLtd\BuildConfigs\Command;

final class InitCommand
{
    public function __invoke(
        string $projectName,
        string $language,
        string $type,
    ): void {
        $projectName = str_replace('.', '-', $projectName);

        // TODO: validate the project type.
        $output = <<<EOF
            name: $projectName
            language: $language
            type: $type
            EOF;

        file_put_contents('build.yaml', $output);
    }
}
