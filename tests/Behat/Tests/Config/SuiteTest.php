<?php

declare(strict_types=1);

namespace Behat\Tests\Config;

use Behat\Config\Filter\NameFilter;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Suite;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
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
        $config->addContext('ThirdContext', ['http://localhost:8080', '/var/tmp']);

        $this->assertEquals([
            'contexts' => [
                'FirstContext',
                'SecondContext',
                [
                    'ThirdContext' => [
                        'http://localhost:8080',
                        '/var/tmp',
                    ],
                ],
            ],
        ], $config->toArray());
    }

    public function testAddingPaths(): void
    {
        $config = new Suite('first');
        $config->withPaths('features/admin/first', 'features/front/first');
        $config->withPaths('features/api/first');

        $this->assertEquals([
            'paths' => [
                'features/admin/first',
                'features/front/first',
                'features/api/first',
            ],
        ], $config->toArray());
    }

    public function testAddingFilters(): void
    {
        $config = new Suite('first');
        $config->withFilter(new TagFilter('tag1'));
        $config->withFilter(new NameFilter('name1'));

        $this->assertEquals([
            'filters' => [
                'tags' => 'tag1',
                'name' => 'name1',
            ],
        ], $config->toArray());
    }

    public function testItThrowsAnExceptionWhenAddingExistingFilter(): void
    {
        $suite = new Suite('first');

        $suite->withFilter(new TagFilter('tag1'));

        $this->expectException(ConfigurationLoadingException::class);
        $this->expectExceptionMessage('The filter "tags" already exists.');

        $suite->withFilter(new TagFilter('tag1'));
    }
}
