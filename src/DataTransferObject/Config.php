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

    #[Assert\Choice(choices: ['node', 'php'])]
    #[Assert\NotBlank]
    public string $language;

    #[Assert\Length(min: 1)]
    #[Assert\NotBlank]
    public string $name;

    #[Assert\Choice(choices: ['drupal', 'fractal', 'laravel', 'php-library', 'vuejs'])]
    #[Assert\NotBlank]
    public string $type;

    #[Assert\NotBlank]
    public ?string $projectRoot;
}
