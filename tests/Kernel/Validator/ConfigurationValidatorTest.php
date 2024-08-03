<?php

namespace App\Tests;

use App\DataTransferObject\ConfigDto;
use App\Enum\ProjectType;
use App\Enum\WebServer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConfigurationValidatorTest extends KernelTestCase
{
    private ConfigDto $configurationDataDto;

    private ValidatorInterface $validator;

    public function setUp(): void
    {
        $this->configurationDataDto = self::createConfigurationDto();

        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    /**
     * @dataProvider extraDatabaseProvider
     */
    public function testThatExtraDatabasesCanBeSpecified(
        ?array $extraDatabases,
        int $expectedViolationCount,
        ?string $expectedMessage,
    ): void {
        $this->configurationDataDto->database = [
            'extra_databases' => $extraDatabases,
            'type' => 'mariadb',
            'version' => 10,
        ];

        $violations = $this->validator->validate($this->configurationDataDto);

        self::assertCount(
            expectedCount: $expectedViolationCount,
            haystack: $violations,
        );

        if ($expectedViolationCount > 0) {
            self::assertSame(
                actual: 'database[extra_databases][0]',
                expected: $violations[0]->getPropertyPath(),
            );

            self::assertSame(
                actual: $expectedMessage,
                expected: $violations[0]->getMessage(),
            );
        }
    }

    /**
     * @dataProvider projectNameProvider
     */
    public function testTheProjectNameShouldBeAString(
        mixed $projectName,
        int $expectedViolationCount,
        ?string $expectedMessage,
    ): void {
        if ($projectName === null) {
            self::expectException(NotNormalizableValueException::class);
        }

        $configurationDataDto = self::createConfigurationDto();
        $configurationDataDto->name = $projectName;

        $violations = $this->validator->validate($configurationDataDto);

        self::assertCount(
            expectedCount: $expectedViolationCount,
            haystack: $violations,
        );

        if ($violations->count() > 0) {
            self::assertSame(
                actual: $expectedMessage,
                expected: $violations[0]->getMessage(),
            );
        }
    }

    /**
     * @dataProvider projectLanguageProvider
     */
    public function testTheProjectLanguageShouldBeASupportedLanguage(
        string $language,
        int $expectedViolationCount,
        ?string $expectedMessage,
    ): void {
        $configurationDataDto = self::createConfigurationDto();
        $configurationDataDto->language = $language;

        $violations = $this->validator->validate($configurationDataDto);

        self::assertCount(
            expectedCount: $expectedViolationCount,
            haystack: $violations,
        );

        if ($expectedViolationCount > 0) {
            self::assertSame(
                actual: $language,
                expected: $violations[0]->getInvalidValue(),
            );

            self::assertSame(
                actual: $expectedMessage,
                expected: $violations[0]->getMessage(),
            );
        }
    }

    /**
     * @dataProvider projectTypeProvider
     */
    public function testTheProjectTypeShouldBeASupportedType(
        string $projectType,
        int $expectedViolationCount,
        ?string $expectedMessage,
    ): void {
        $configurationDataDto = self::createConfigurationDto();
        $configurationDataDto->type = $projectType;

        $violations = $this->validator->validate($configurationDataDto);

        self::assertCount(
            expectedCount: $expectedViolationCount,
            haystack: $violations,
        );

        if ($expectedViolationCount > 0) {
            self::assertSame(
                actual: $projectType,
                expected: $violations[0]->getInvalidValue(),
            );

            self::assertSame(
                actual: $expectedMessage,
                expected: $violations[0]->getMessage(),
            );
        }
    }

    /**
     * @dataProvider validWebServerTypes
     */
    public function testTheWebServerTypeIsValid(
        string $webServer,
        int $expectedViolationCount,
        ?string $expectedMessage,
    ): void {
        $configurationDataDto = self::createConfigurationDto();
        $configurationDataDto->web['type'] = $webServer;

        $violations = $this->validator->validate($configurationDataDto);

        self::assertCount(
            expectedCount: $expectedViolationCount,
            haystack: $violations,
        );

        if ($expectedViolationCount > 0) {
            self::assertSame(
                actual: $webServer,
                expected: $violations[0]->getInvalidValue(),
            );

            self::assertSame(
                actual: $expectedMessage,
                expected: $violations[0]->getMessage(),
            );
        }
    }

    public static function extraDatabaseProvider(): \Generator
    {
        return [
            yield 'correct' => [['migrate'], 0, null],
            yield 'empty string' => [[''], 1, 'This value should not be blank.'],
            yield 'missing' => [null, 0, null],
            yield 'no extra databases' => [[], 0, null],
        ];
    }

    public static function projectLanguageProvider(): \Generator
    {
        return [
            yield 'Supported language string' => ['php', 0, null],
            yield 'Non-supported language string' => ['not-supported', 1, 'The value you selected is not a valid choice.'],
            yield 'Empty string' => ['', 1, 'The value you selected is not a valid choice.'],
        ];
    }

    public static function projectNameProvider(): \Generator
    {
        return [
            yield 'Non-empty string' => ['test', 0, null],
            yield 'Empty string' => ['', 1, 'This value should not be blank.'],
        ];
    }

    public static function projectTypeProvider(): \Generator
    {
        return [
            yield 'drupal' => [ProjectType::Drupal->value, 0, null],
            yield 'fractal' => [ProjectType::Fractal->value, 0, null],
            yield 'invalid' => ['not-a-project-type', 1, 'The value you selected is not a valid choice.'],
            yield 'laravel' => [ProjectType::Laravel->value, 0, null],
            yield 'php-library' => [ProjectType::PHPLibrary->value, 0, null],
            yield 'symfony' => [ProjectType::Symfony->value, 0, null],
        ];
    }

    public static function validWebServerTypes(): \Generator
    {
        return [
            yield 'caddy' => [WebServer::Caddy->value, 0, null],
            yield 'invalid' => ['not-a-valid-web-server', 1, 'The value you selected is not a valid choice.'],
            yield 'nginx' => [WebServer::Nginx->value, 0, null],
        ];
    }

    private static function createConfigurationDto(): ConfigDto
    {
        $configurationDataDto = new ConfigDto();
        $configurationDataDto->language = 'php';
        $configurationDataDto->name = 'test';
        $configurationDataDto->type = 'drupal';

        return $configurationDataDto;
    }
}
