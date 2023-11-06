<?php

declare(strict_types=1);

namespace App\DataTransferObject;

use Symfony\Component\Validator\Constraints as Assert;

final class Config
{
    /**
     * @var array<string,string|integer|array<int,string>>
     */
    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'extra_databases' => new Assert\Optional(
                new Assert\All(new Assert\Type('string'))
            ),
            'type' => new Assert\Choice(['mariadb', 'mysql']),
            'version' => new Assert\Type('integer'),
        ],
    )]
    public array $database;

    /**
     * @var array<string,string|null>
     */
    #[Assert\Collection(
        allowExtraFields: false,
        fields: ['docroot' => new Assert\Choice([null, 'web', 'docroot'])],
    )]
    public array $drupal;

    /**
     * @var array<string,string|null>
     */
    #[Assert\Collection(
        allowExtraFields: false,
        allowMissingFields: true,
        fields: [
            'createGitHubActionsConfiguration' => new Assert\Type('boolean'),
            'runGitHooksBeforePush' => new Assert\Type('boolean'),
            'useNewDatabaseCredentials' => new Assert\Type('boolean'),
        ]
    )]
    public array $experimental;

    #[Assert\Choice(choices: ['javascript', 'php', 'typescript'])]
    public string $language;

    #[Assert\NotBlank]
    public string $name;

    #[Assert\Choice(choices: ['astro', 'drupal', 'fractal', 'laravel', 'php-library', 'terraform', 'vuejs'])]
    #[Assert\NotBlank]
    public string $type;

    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'ignore' => new Assert\Optional([
                new Assert\Type('array'),
                new Assert\Count(['min' => 1]),
                new Assert\All([
                    new Assert\Type('string'),
                ])
            ])
        ]
    )]
    public array $git;

    public array $php;
}
