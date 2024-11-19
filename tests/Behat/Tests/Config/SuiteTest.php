<?php

namespace Behat\Tests\Config;

use Behat\Config\Suite;
use PHPUnit\Framework\TestCase;

final class SuiteTest extends TestCase
{
    public function testSuiteCanBeConvertedIntoAnArray(): void
    {
        $suite = new Suite('first');

        $this->assertIsArray($suite->toArray());
    }

    public function testItReturnsSettings(): void
    {
        $settings = [
            'contexts' => [
                'FirstContext',
            ],
        ];

        $suite = new Suite('first', $settings);

        $this->assertEquals($settings, $suite->toArray());
    }

    public function testAddingContexts(): void
    {
        $config = new Suite('first');
        $config->withContexts('FirstContext', 'SecondContext');
        $config->withContexts('ThirdContext');

        $this->assertEquals([
            'contexts' => [
                'FirstContext',
                'SecondContext',
                'ThirdContext',
            ]
        ], $config->toArray());
    }
}
