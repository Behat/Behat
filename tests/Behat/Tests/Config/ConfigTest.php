<?php

declare(strict_types=1);

namespace Behat\Tests\Config;

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testConfigCanBeConvertedIntoAnArray(): void
    {
        $config = new Config();

        $this->assertIsArray($config->toArray());
    }

    public function testItReturnsSettings(): void
    {
        $settings = [
            'default' => [
                'gherkin' => [
                    'filters' => [
                        'tags' => '~@test',
                    ],
                ],
            ],
        ];

        $config = new Config($settings);

        $this->assertEquals($settings, $config->toArray());
    }

    public function testAddingImports(): void
    {
        $config = new Config();
        $config
            ->import('config/first_suite.php')
            ->import('config/second_suite.php')
        ;

        $this->assertEquals([
            'imports' => [
                'config/first_suite.php',
                'config/second_suite.php',
            ],
        ], $config->toArray());
    }

    public function testAddingProfile(): void
    {
        $config = new Config();

        $config->withProfile(new Profile('default', [
            'extensions' => [
                'some_extension' => [],
            ],
        ]));

        $this->assertEquals([
            'default' => [
                'extensions' => [
                    'some_extension' => [],
                ],
            ],
        ], $config->toArray());
    }

    public function testItThrowsAnExceptionWhenAddingExistingProfile(): void
    {
        $config = new Config();

        $config->withProfile(new Profile('default'));

        $this->expectException(ConfigurationLoadingException::class);
        $this->expectExceptionMessage('The profile "default" already exists.');

        $config->withProfile(new Profile('default', [
            'extensions' => [
                'some_extension' => [],
            ],
        ]));
    }
}
