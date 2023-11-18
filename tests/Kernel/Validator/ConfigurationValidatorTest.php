<?php

namespace App\Tests;

use App\DataTransferObject\Config;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConfigurationValidatorTest extends KernelTestCase
{
    private SerializerInterface $serializer;

    private ValidatorInterface $validator;

    public function setUp(): void
    {
        $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());

        $this->serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        $this->validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
    }

    /**
     * @dataProvider projectNameProvider
     */
    public function testTheProjectNameShouldBeAString(mixed $projectName, int $expectedViolationCount): void
    {
        if ($projectName === NULL) {
            self::expectException(NotNormalizableValueException::class);
        }

        $configurationData = [
            'language' => 'php',
            'name' => $projectName,
            'type' => 'drupal',
        ];

        $configurationDataDto = $this->createConfigurationDTO($configurationData);

        $violations = $this->validator->validate($configurationDataDto);

        self::assertCount(
            expectedCount: $expectedViolationCount,
            haystack: $violations,
        );
    }

    /**
     * @dataProvider projectLanguageProvider
     */
    public function testTheProjectLanguageShouldBeASupportedLanguage(mixed $language, int $expectedViolationCount): void
    {
        if ($language === NULL) {
            self::expectException(NotNormalizableValueException::class);
        }

        $configurationData = [
            'language' => $language,
            'name' => 'test',
            'type' => 'drupal',
        ];

        $configurationDataDto = $this->createConfigurationDTO($configurationData);

        $violations = $this->validator->validate($configurationDataDto);

        self::assertCount(
            expectedCount: $expectedViolationCount,
            haystack: $violations,
        );
    }

    public function projectLanguageProvider(): \Generator
    {
        return [
            yield 'Supported language string' => ['php', 0],
            yield 'Non-supported language string' => ['not-supported', 1],
            yield 'Empty string' => ['', 1],
            yield 'True' => [true, 1],
            yield 'False' => [false, 1],
            // yield 'Integer' => [1, 2],
            yield 'Null' => [null, 1],
        ];
    }

    public function projectNameProvider(): \Generator
    {
        return [
            yield 'Non-empty string' => ['test', 0],
            yield 'Empty string' => ['', 1],
            yield 'False' => [false, 1],
            yield 'Null' => [null, 1],
        ];
    }

    private function createConfigurationDTO(array $configurationData): Config
    {
        return $this->serializer->deserialize(json_encode($configurationData), Config::class, 'json');
    }
}
