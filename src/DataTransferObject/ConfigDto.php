<?php

declare(strict_types=1);

namespace App\DataTransferObject;

use Symfony\Component\Validator\Constraints as Assert;

final class ConfigDto
{
    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'extra_databases' => new Assert\Optional([
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\NotBlank(),
                    new Assert\Type('string'),
                ]),
            ]),

            'type' => new Assert\Required([
                new Assert\Choice(choices: ['mariadb', 'mysql']),
            ]),

            'version' => new Assert\Required([
                new Assert\Type('int'),
            ]),
        ],
    )]
    public array $database;

    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'services' => new Assert\Required([
                new Assert\Type('array'),
                new Assert\Count(['min' => 1]),
                new Assert\All([
                    new Assert\Type('string'),
                ]),
            ]),
        ],
    )]
    public array $dockerCompose;

    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'stages' => new Assert\Optional([
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\Collection([
                        'commands' => new Assert\Optional([
                            new Assert\Type('array'),
                            new Assert\All([
                                new Assert\Type('string'),
                            ]),
                        ]),

                        'extra_directories' => new Assert\Optional([
                            new Assert\Type('array'),
                            new Assert\All([
                                new Assert\Type('string'),
                            ]),
                        ]),

                        'extends' => new Assert\Optional([
                            new Assert\Type('string'),
                        ]),

                        'extensions' => new Assert\Optional([
                            new Assert\Collection(
                                allowExtraFields: false,
                                fields: [
                                    'install' => new Assert\Required([
                                        new Assert\Type('array'),
                                        new Assert\All([
                                            new Assert\Type('string'),
                                        ]),
                                    ]),
                                ],
                            ),
                        ]),

                        'packages' => new Assert\Optional([
                            new Assert\Type('array'),
                            new Assert\All([
                                new Assert\Type('string'),
                            ]),
                        ]),

                        'root_commands' => new Assert\Optional([
                            new Assert\Type('array'),
                            new Assert\All([
                                new Assert\Type('string'),
                            ]),
                        ]),
                    ]),
                ]),
            ]),
        ],
    )]
    public array $dockerfile;

    #[Assert\Collection(
        allowExtraFields: false,
        fields: ['docroot' => new Assert\Choice([null, 'web', 'docroot'])],
    )]
    public array $drupal;

    #[Assert\Collection(
        allowExtraFields: true,
        fields: [
            'createGitHubActionsConfiguration' => new Assert\Optional([
                new Assert\Type('bool'),
            ]),

            'createInclusiveGitIgnoreFile' => new Assert\Optional([
                new Assert\Type('bool'),
            ]),

            'createTmuxStartupFile' => new Assert\Optional([
                new Assert\Type('bool'),
            ]),

            'runGitHooksBeforePush' => new Assert\Optional([
                new Assert\Type('bool'),
            ]),

            'runStaticAnalysisOnTests' => new Assert\Optional([
                new Assert\Type('bool'),
            ]),
        ],
    )]
    public array $experimental;

    #[Assert\Collection([
        'ignore' => new Assert\Optional([
            new Assert\All([
                new Assert\Type('string'),
            ]),
        ]),
    ])]
    public array $git;

    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'devshell' => new Assert\Required([
                new Assert\Type('array'),
                new Assert\Collection([
                    'packages' => new Assert\Required([
                        new Assert\Type('array'),
                        new Assert\All([
                            new Assert\Type('string'),
                        ]),
                    ]),
                ]),
            ]),
        ],
    )]
    public array $flake;

    #[Assert\Choice(choices: ['javascript', 'php', 'typescript'])]
    public string $language;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $name;

    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'version' => new Assert\Required([
                new Assert\Type('string'),
            ]),
        ],
    )]
    public array $node;

    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'version' => new Assert\Required([
                new Assert\Type('string'),
            ]),

            'phpcs' => new Assert\Optional([
                new Assert\AtLeastOneOf(
                    constraints: [
                        new Assert\IsFalse(),
                        new Assert\Collection([
                            'paths' => new Assert\Required([
                                new Assert\Type('array'),
                                new Assert\Count(['min' => 1]),
                                new Assert\All([
                                    new Assert\Type('string'),
                                ]),
                            ]),

                            'standards' => new Assert\Required([
                                new Assert\Type('array'),
                                new Assert\Count(['min' => 1]),
                                new Assert\All([
                                    new Assert\Type('string'),
                                ]),
                            ]),
                        ]),
                    ]
                ),
            ]),

            'phpstan' => new Assert\Optional(
                new Assert\AtLeastOneOf(
                    constraints: [
                        new Assert\IsFalse(),
                        new Assert\Collection([
                            'baseline' => new Assert\Optional([
                                new Assert\Type('boolean'),
                            ]),

                            'level' => new Assert\Required([
                                new Assert\Type(['string', 'integer']),
                            ]),

                            'paths' => new Assert\Required([
                                new Assert\Type('array'),
                                new Assert\Count(['min' => 1]),
                                new Assert\All([
                                    new Assert\Type('string'),
                                ]),
                            ]),
                        ]),
                    ]
                ),
            ),

            'phpunit' => new Assert\Optional(
                new Assert\IsFalse(),
            ),
        ],
    )]
    public array $php;

    #[Assert\Type('string')]
    public string $projectRoot;

    #[Assert\Choice(choices: ['drupal', 'fractal', 'laravel', 'php-library', 'sculpin', 'symfony', 'terraform'])]
    public string $type;

    #[Assert\Collection([
        'type' => new Assert\Required([
            new Assert\Choice(choices: ['nginx', 'caddy']),
        ]),
    ])]
    public array $web;

    public bool $isDocker;

    public bool $isFlake;
}
