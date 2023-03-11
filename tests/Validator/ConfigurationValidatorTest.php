<?php

use OliverDaviesLtd\BuildConfigs\Validator\ConfigurationValidator;

beforeEach(function (): void {
    $this->validator = new ConfigurationValidator();
});

test('The project name should be a string', function (mixed $projectName, int $expectedViolationCount) {
    $configuration = [
        'name' => $projectName,
    ];

    expect($this->validator->validate($configuration))
        ->toHaveCount($expectedViolationCount);
})->with(function () {
    yield 'Non-empty string' => ['test', 0];
    yield 'Empty string' => ['', 1];
    yield 'Integer' => [1, 1];
    yield 'Null' => [null, 1];
    yield 'True' => [true, 1];
    yield 'False' => [false, 2];
});
