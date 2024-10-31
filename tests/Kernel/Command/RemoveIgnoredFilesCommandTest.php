<?php

use App\Command\RemoveIgnoredFilesCommand;
use App\DataTransferObject\TemplateFile;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RemoveIgnoredFilesCommandTest extends KernelTestCase
{
    public function test_it_removes_any_ignored_files(): void
    {
        $filenamesToGenerate = collect([
            new TemplateFile(data: '', name: 'phpcs.xml.dist'),
            new TemplateFile(data: '', name: 'phpstan.neon.dist'),
        ]);

        $filenamesToIgnore = ['phpstan.neon.dist'];

        $command = new RemoveIgnoredFilesCommand($filenamesToIgnore);

        $command->execute([[], [], $filenamesToGenerate], function ($result) {
            self::assertCount(1, $result[1]);
            self::assertSame('phpcs.xml.dist', $result[1][0]->name);
        });
    }
}
