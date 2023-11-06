<?php

namespace App\Tests;

use App\DataTransferObject\Config;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
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
     * @test
     */
    public function project_name_should_be_a_string(string|int|bool|null $projectName, int $expectedViolationCount): void
    {
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
     * @test
     */
    public function the_project_language_should_be_a_supported_language(string|int|bool|null $language, int $expectedViolationCount): void
    {
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
            // yield 'Null' => [null, 1],
        ];
    }

    public function projectNameProvider(): \Generator
    {
        return [
            yield 'Non-empty string' => ['test', 0],
            yield 'Empty string' => ['', 1],
            yield 'False' => [false, 1],
            // yield 'Null' => [null, 1],
        ];
    }

    private function createConfigurationDTO(array $configurationData): Config
    {
        return $this->serializer->deserialize(json_encode($configurationData), Config::class, 'json');
    }
}
