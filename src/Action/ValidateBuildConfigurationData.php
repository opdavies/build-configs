<?php

declare(strict_types=1);

namespace App\Action;

use App\DataTransferObject\Config;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;

final class ValidateBuildConfigurationData
{
    public function handle(array $configurationData, \Closure $next) {
        // Convert the input to a configuration data object.
        $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);
        $configurationDataObject = $serializer->deserialize(json_encode($configurationData), Config::class, 'json');

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($configurationDataObject);

        if (0 < $violations->count()) {
            $io->error('Configuration is invalid.');

            $io->listing(
                collect($violations)
                    ->map(fn (ConstraintViolationInterface $v) => "{$v->getPropertyPath()} - {$v->getMessage()}")
                    ->toArray()
            );

            return;
        }
        
        return $next($configurationData);
    }
}
