<?php

declare(strict_types=1);

namespace OliverDaviesLtd\BuildConfigs;

use Symfony\Component\Validator\Constraints as Assert;

final class ConfigurationData
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

    #[Assert\Choice(choices: ['node', 'php'])]
    #[Assert\NotBlank]
    public string $language;

    #[Assert\Length(min: 1)]
    #[Assert\NotBlank]
    public string $name;

    #[Assert\Choice(choices: ['drupal-project', 'fractal', 'laravel', 'php-library'])]
    #[Assert\NotBlank]
    public string $type;

    #[Assert\NotBlank]
    public ?string $projectRoot;
}
