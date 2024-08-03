<?php

declare(strict_types=1);

namespace App\Command;

use App\DataTransferObject\ConfigDto;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;

final class ValidateConfigurationDataCommand
{
    public function execute(array $configurationData, \Closure $next)
    {
        // Convert the input to a configuration data object.
        $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        $configurationDataDto = $serializer->deserialize(json_encode($configurationData), ConfigDto::class, 'json');

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($configurationDataDto);

        if (0 < $violations->count()) {
            throw new \RuntimeException('Configuration is invalid.');
        }

        return $next([$configurationData, $configurationDataDto]);
    }
}
