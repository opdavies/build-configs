<?php

declare(strict_types=1);

namespace OliverDaviesLtd\BuildConfigs;

use Symfony\Component\Validator\Constraints as Assert;

final class ConfigurationData
{
    #[Assert\Collection(
        allowExtraFields: false,
        fields: [
            'extra_databases' => new Assert\Optional(
                new Assert\All(new Assert\Type('string'))
            ),
            'type' => new Assert\Choice(['mariadb', 'mysql']),
            'version' => new Assert\Type('integer'),
        ],
        allowExtraFields: false,
    )]
    public array $database;

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
