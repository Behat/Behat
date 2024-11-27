<?php

declare(strict_types=1);

namespace Behat\Tests\Config;

use Behat\Config\Profile;
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
}
