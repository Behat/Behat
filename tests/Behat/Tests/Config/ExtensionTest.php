<?php

declare(strict_types=1);

namespace Behat\Tests\Config;

use Behat\Config\Config;
use Behat\Config\Extension;
use PHPUnit\Framework\TestCase;

final class ExtensionTest extends TestCase
{
    public function testExtensionCanBeConvertedIntoAnArray(): void
    {
        $extension = new Extension('custom_extension');

        $this->assertIsArray($extension->toArray());
    }

    public function testItReturnsSettings(): void
    {
        $settings = [
            'base_url' => 'https://127.0.0.1:8080',
        ];

        $extension = new Extension('Behat\MinkExtension', $settings);

        $this->assertEquals($settings, $extension->toArray());
    }
}
