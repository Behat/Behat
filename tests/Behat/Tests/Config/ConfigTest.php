<?php

namespace Behat\Tests\Config;

use Behat\Config\Config;
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
                        'tags' => '~@php8'
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
}
