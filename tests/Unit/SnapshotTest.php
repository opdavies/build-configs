<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * @group snapshots
 */
class SnapshotTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private static $configs = [
        'drupal',
        'drupal-commerce-kickstart',
        'drupal-localgov',
        'fractal',
    ];

    public function testCompareFiles(): void
    {
        foreach (self::$configs as $config) {
            $baseDir = getcwd() . "/tests/snapshots/output/{$config}";
            $generatedDir = getcwd() . "/.ignored/snapshots/output/{$config}";

            $this->runCliTool($config);

            $baseFiles = $this->getFiles($baseDir);

            foreach ($baseFiles as $file) {
                $this->assertFileEquals(
                    expected: $baseDir . '/' . $file,
                    actual: $generatedDir . '/' . $file,
                    message: "Files do not match: {$file}",
                );
            }
        }
    }

    private function runCliTool(string $config): void
    {
        $configFilePath = getcwd() . "/tests/snapshots/configs/{$config}.yaml";

        $cliCommand = sprintf(
            "%s app:generate --config-file %s --output-dir %s",
            getcwd() . '/bin/build-configs',
            $configFilePath,
            getcwd() . "/.ignored/snapshots/output/{$config}",
        );

        exec($cliCommand);
    }

    /**
     * @return array<int, string>
     */
    private function getFiles(string $directory): array
    {
        $files = [];

        $finder = new Finder();
        $finder->in($directory)->files();

        foreach ($finder as $file) {
            $files[] = $file->getRelativePathname();
        }

        return $files;
    }
}
