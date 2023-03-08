<?php

declare(strict_types=1);

namespace OliverDaviesLtd\BuildConfigs\Validator;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ValidatorInterface
{
    /**
     * @param array<string,mixed> $configurationData
     */
    public function validate(array $configurationData): ConstraintViolationListInterface;
}
