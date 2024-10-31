<?php

use App\IgnoreFile;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IgnoreFileTest extends KernelTestCase
{
    public function tearDown(): void
    {
        @unlink(IgnoreFile::FILENAME);

        parent::tearDown();
    }

    public function test_it_parses_a_list_of_file_names_from_an_ignore_file(): void
    {
        file_put_contents(IgnoreFile::FILENAME, join("\n", ['phpstan.neon.dist']));

        self::assertSame(['phpstan.neon.dist'], IgnoreFile::parse());
    }
}
