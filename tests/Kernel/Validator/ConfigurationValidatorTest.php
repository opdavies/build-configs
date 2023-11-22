<?php

namespace App\Tests;

use App\DataTransferObject\Config;
use App\Enum\ProjectType;
use App\Enum\WebServer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConfigurationValidatorTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    public function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
    }

    /**
     * @dataProvider projectNameProvider
     */
    public function testTheProjectNameShouldBeAString(
        mixed $projectName,
        int $expectedViolationCount,
    ): void {
        if ($projectName === null) {
            self::expectException(NotNormalizableValueException::class);
        }

        $configurationDataDTO = self::createConfigurationDTO();
        $configurationDataDTO->name = $projectName;

        $violations = $this->validator->validate($configurationDataDTO);

        self::assertCount(
            expectedCount: $expectedViolationCount,
            haystack: $violations,
        );
    }

    /**
     * @dataProvider projectLanguageProvider
     */
    public function testTheProjectLanguageShouldBeASupportedLanguage(
        string $language,
        int $expectedViolationCount,
    ): void {
        $configurationDataDTO = self::createConfigurationDTO();
        $configurationDataDTO->language = $language;

        $violations = $this->validator->validate($configurationDataDTO);

        self::assertCount(
            expectedCount: $expectedViolationCount,
            haystack: $violations,
        );

        if ($expectedViolationCount > 0) {
            self::assertSame(
                actual: $language,
                expected: $violations[0]->getInvalidValue(),
            );
        }
    }

    /**
     * @dataProvider projectTypeProvider
     */
    public function testTheProjectTypeShouldBeASupportedType(
        string $projectType,
        int $expectedViolationCount,
    ): void {
        $configurationDataDTO = self::createConfigurationDTO();
        $configurationDataDTO->type = $projectType;

        $violations = $this->validator->validate($configurationDataDTO);

        self::assertCount(
            expectedCount: $expectedViolationCount,
            haystack: $violations,
        );

        if ($expectedViolationCount > 0) {
            self::assertSame(
                actual: $projectType,
                expected: $violations[0]->getInvalidValue(),
            );
        }
    }

    /**
     * @dataProvider validWebServerTypesProvider
     */
    public function testTheWebServerTypeIsValid(
        string $webServer,
        int $expectedViolationCount,
    ): void {
        $configurationDataDTO = self::createConfigurationDTO();
        $configurationDataDTO->web['type'] = $webServer;

        $violations = $this->validator->validate($configurationDataDTO);

        self::assertCount(
            expectedCount: $expectedViolationCount,
            haystack: $violations,
        );

        if ($expectedViolationCount > 0) {
            self::assertSame(
                actual: $webServer,
                expected: $violations[0]->getInvalidValue(),
            );
        }
    }

    public function projectLanguageProvider(): \Generator
    {
        return [
            yield 'Supported language string' => ['php', 0],
            yield 'Non-supported language string' => ['not-supported', 1],
            yield 'Empty string' => ['', 1],
        ];
    }

    public function projectNameProvider(): \Generator
    {
        return [
            yield 'Non-empty string' => ['test', 0],
            yield 'Empty string' => ['', 1],
        ];
    }

    public function projectTypeProvider(): \Generator
    {
        return [
            yield 'astro' => [ProjectType::Astro->value, 0],
            yield 'drupal' => [ProjectType::Drupal->value, 0],
            yield 'fractal' => [ProjectType::Fractal->value, 0],
            yield 'invalid' => ['not-a-project-type', 1],
            yield 'laravel' => [ProjectType::Laravel->value, 0],
            yield 'php-library' => [ProjectType::PHPLibrary->value, 0],
            yield 'symfony' => [ProjectType::Symfony->value, 0],
            yield 'terraform' => [ProjectType::Terraform->value, 0],
        ];
    }

    public function validWebServerTypesProvider(): \Generator
    {
        return [
            yield 'caddy' => [WebServer::Caddy->value, 0],
            yield 'invalid' => ['not-a-valid-web-server', 1],
            yield 'nginx' => [WebServer::Nginx->value, 0],
        ];
    }

    private static function createConfigurationDTO(): Config
    {
        $configurationDataDTO = new Config();
        $configurationDataDTO->language = 'php';
        $configurationDataDTO->name = 'test';
        $configurationDataDTO->type = 'drupal';

        return $configurationDataDTO;
    }
}
