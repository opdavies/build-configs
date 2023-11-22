<?php

declare(strict_types=1);

namespace App\DataTransferObject;

use Symfony\Component\Validator\Constraints as Assert;

final class Config
{
    #[Assert\Type('array')]
    #[Assert\Valid]
    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'extra_databases' => new Assert\Optional([
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\Type('string'),
                ]),
            ]),
            'type' => new Assert\Required([
                new Assert\Choice(choices: ['mariadb', 'mysql']),
                new Assert\Type('string'),
            ]),
            'version' => new Assert\Required([
                new Assert\Type('int'),
            ]),
        ],
    )]
    public array $database;

    #[Assert\Type('array')]
    #[Assert\Valid]
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

    #[Assert\Type('array')]
    #[Assert\Valid]
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

    #[Assert\Type('array')]
    #[Assert\Valid]
    #[Assert\Collection(
        allowExtraFields: false,
        fields: ['docroot' => new Assert\Choice([null, 'web', 'docroot'])],
    )]
    public array $drupal;

    #[Assert\Type('array')]
    #[Assert\Valid]
    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'createGitHubActionsConfiguration' => new Assert\Optional([
                new Assert\Type('bool'),
            ]),
            'runGitHooksBeforePush' => new Assert\Optional([
                new Assert\Type('bool'),
            ]),
            // TODO: remove this when its been removed from all `build.yaml` files.
            'useNewDatabaseCredentials' => new Assert\Optional([
                new Assert\Type('bool'),
            ]),
        ],
    )]
    public array $experimental;

    #[Assert\Type('array')]
    #[Assert\Valid()]
    #[Assert\Collection([
        'ignore' => new Assert\Optional([
            new Assert\All([
                new Assert\Type('string'),
            ]),
        ]),
    ])]
    public array $git;

    #[Assert\Type('array')]
    #[Assert\Valid]
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

    #[Assert\Type('string')]
    #[Assert\Choice(choices: ['javascript', 'php', 'typescript'])]
    public string $language;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $name;

    #[Assert\Type('array')]
    #[Assert\Valid]
    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'version' => new Assert\Required([
                new Assert\Type('string'),
            ]),
        ],
    )]
    public array $node;

    #[Assert\Type('array')]
    #[Assert\Valid]
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

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Choice(choices: ['astro', 'drupal', 'fractal', 'laravel', 'php-library', 'symfony', 'terraform'])]
    public string $type;

    #[Assert\Type('array')]
    #[Assert\Valid()]
    #[Assert\Collection([
        'type' => new Assert\Required([
            new Assert\Type('string'),
            new Assert\Choice(choices: ['nginx', 'caddy']),
        ]),
    ])]
    public array $web;
}
