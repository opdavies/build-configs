<?php

declare(strict_types=1);

namespace OliverDaviesLtd\BuildConfigs\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

final class ConfigurationValidator implements ValidatorInterface
{
    public function validate(array $configurationData): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        $groups = new Assert\GroupSequence(['Default', 'custom']);

        $constraint = new Assert\Collection(
            [
                'fields' => [
                    'name' => [
                        new Assert\NotNull(),
                        new Assert\Type('string'),
                        new Assert\Length(['min' => 1]),
                    ],

                    'language' => [
                        new Assert\NotNull(),
                        new Assert\Type('string'),
                        new Assert\Choice([
                            'node',
                            'php',
                        ]),
                    ],

                    'type' => [
                        new Assert\NotNull(),
                        new Assert\Type('string'),
                        new Assert\Choice([
                            'drupal-project',
                            'fractal',
                            'laravel',
                            'php-library',
                        ]),
                    ],

                    'project_root' => [
                        new Assert\NotNull(),
                        new Assert\Type('string'),
                    ],

                    'database' => new Assert\Collection(
                        [
                            'extra_databases' => new Assert\Optional(
                                [
                                    new Assert\Type(['type' => 'array']),
                                    new Assert\All(
                                        [
                                            new Assert\Type(['type' => 'string'])
                                        ]
                                    )
                                ],
                            ),
                            'type' => new Assert\Type(['type' => 'string']),
                            'version' => new Assert\Type(['type' => 'integer']),
                        ]
                    ),

                    'drupal' => new Assert\Optional(),

                    'docker-compose' => new Assert\Optional(),

                    'dockerfile' => new Assert\Optional(),

                    // TODO: this should be a boolean if present.
                    'justfile' => new Assert\Optional(),

                    'node' => new Assert\Optional(),

                    'php' => new Assert\Optional(),

                    'web' => new Assert\Optional(),
                ],

                'allowExtraFields' => false,
                'allowMissingFields' => true,
            ],
        );

        return $validator->validate(
            constraints: $constraint,
            groups: $groups,
            value: $configurationData,
        );
    }
}
