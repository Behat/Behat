<?php

declare(strict_types=1);

namespace Behat\Tests\Config;

use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use PHPUnit\Framework\TestCase;

final class ProfileTest extends TestCase
{
    public function testProfileCanBeConvertedIntoAnArray(): void
    {
        $profile = new Profile('default');

        $this->assertIsArray($profile->toArray());
    }

    public function testItReturnsSettings(): void
    {
        $settings = [
            'extensions' => [
                'some_extension' => [],
            ],
        ];

        $profile = new Profile('default', $settings);

        $this->assertEquals($settings, $profile->toArray());
    }

    public function testAddingExtensions(): void
    {
        $profile = new Profile('default');
        $profile->withExtension(new Extension('Behat\MinkExtension', ['base_url' => 'https://127.0.0.1:8080']));
        $profile->withExtension(new Extension('FriendsOfBehat\MinkDebugExtension', ['directory' => 'etc/build']));

        $this->assertEquals([
            'extensions' => [
                'Behat\MinkExtension' => [
                    'base_url' => 'https://127.0.0.1:8080',
                ],
                'FriendsOfBehat\MinkDebugExtension' => [
                    'directory' => 'etc/build',
                ],
            ],
        ], $profile->toArray());
    }

    public function testAddingSuites(): void
    {
        $profile = new Profile('default');
        $profile
            ->withSuite(new Suite('admin_dashboard'))
            ->withSuite(new Suite('managing_administrators'))
        ;

        $this->assertEquals([
            'suites' => [
                'admin_dashboard' => [],
                'managing_administrators' => [],
            ],
        ], $profile->toArray());
    }

    public function testItThrowsAnExceptionWhenAddingExistingSuite(): void
    {
        $profile = new Profile('default');

        $profile->withSuite(new Suite('admin_dashboard'));

        $this->expectException(ConfigurationLoadingException::class);
        $this->expectExceptionMessage('The suite "admin_dashboard" already exists.');

        $profile->withSuite(new Suite('admin_dashboard'));
    }
}
