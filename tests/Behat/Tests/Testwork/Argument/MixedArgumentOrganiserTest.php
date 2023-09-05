<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Tests\Testwork\Argument;

use Behat\Testwork\Argument\MixedArgumentOrganiser;
use PHPUnit\Framework\TestCase;

final class MixedArgumentOrganiserTest extends TestCase
{
    private $organiser;

    public function setUp(): void
    {
        $this->organiser = new MixedArgumentOrganiser();
    }

    /** @test */
    public function itOrganisesNothingIfNoArgs()
    {
        $r = new \ReflectionFunction(
            function (\DateTimeInterface $d) {}
        );
        $args = [];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([], $organised);
    }

    /** @test */
    public function itMatchesArgsByPosition()
    {
        $r = new \ReflectionFunction(
            function ($x, $y) {}
        );
        $args = [
            1,
            2,
            3,
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([1, 2], $organised);
    }

    /** @test */
    public function itMatchesArgsByName()
    {
        $r = new \ReflectionFunction(
            function ($date) {}
        );
        $args = [
            'date' => $date = new \DateTime(),
            'x' => new \stdClass(),
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame(['date' => $date], $organised);
    }

    /** @test */
    public function itMatchesArgsByType()
    {
        $r = new \ReflectionFunction(
            function (\DateTimeInterface $d) {}
        );
        $args = [
            'x' => $date = new \DateTime(),
            'y' => new \stdClass(),
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([$date], $organised);
    }

    /** @test */
    public function itMatchesArgsByNameOverType()
    {
        $r = new \ReflectionFunction(
            function (\DateTimeInterface $a, $date) {}
        );
        $args = [
            'date' => $date = new \DateTime(),
            'x' => 1,
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([1, 'date' => $date], $organised);
    }

    /**
     * @test
     * @requires PHP >= 8.0
     */
    public function itMatchesUnionTypes()
    {
        $r = eval(<<<'CODE'
            return new \ReflectionFunction(
              function(int|\DateTimeInterface $a) {}
            );
CODE
        );
        $args = [
            'date' => $date = new \DateTime(),
            'x' => 1,
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([$date], $organised);
    }
}
