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

test('The project language should be a supported language', function (mixed $language, int $expectedViolationCount) {
    $configuration = [
        'language' => $language,
    ];

    expect($this->validator->validate($configuration))
        ->toHaveCount($expectedViolationCount);
})->with(function () {
    yield 'Supported language string' => ['php', 0];
    yield 'Non-supported language string' => ['not-supported', 1];
    yield 'Empty string' => ['', 1];
    yield 'Integer' => [1, 2];
    yield 'Null' => [null, 1];
    yield 'True' => [true, 2];
    yield 'False' => [false, 2];
});
