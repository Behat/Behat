<?php

namespace Behat\Tests\Testwork\Argument;

use Behat\Testwork\Argument\MixedArgumentOrganiser;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

final class MixedArgumentOrganiserTest extends TestCase
{
    private $organiser;

    protected function setUp() : void
    {
        $this->organiser = new MixedArgumentOrganiser();
    }

    public function testThatItOrganisesNothingIfNoArgs(): void
    {
        $r = new ReflectionFunction(
            static function(\DateTimeInterface $d) {}
        );
        $args = [];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([], $organised);
    }

    public function testThatItMatchesArgsByPosition(): void
    {
        $r = new ReflectionFunction(
            static function($x, $y) {}
        );
        $args = [
            1,
            2,
            3
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([1, 2], $organised);
    }

    public function testThatItMatchesArgsByName(): void
    {
        $r = new ReflectionFunction(
            static function($date) {}
        );
        $args = [
            'date' => $date = new \DateTime(),
            'x' => new \stdClass()
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame(['date' => $date], $organised);
    }

   public function testThatItMatchesArgsByType(): void
    {
        $r = new ReflectionFunction(
            static function(\DateTimeInterface $d) {}
        );
        $args = [
            'x' => $date = new \DateTime(),
            'y' => new \stdClass()
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([$date], $organised);
    }

    public function testThatItMatchesArgsByNameOverType(): void
    {
        $r = new ReflectionFunction(
            static function(\DateTimeInterface $a, $date) {}
        );
        $args = [
            'date' => $date = new \DateTime(),
            'x' => 1
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([1, 'date' => $date], $organised);
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testThatItMatchesUnionTypes(): void
    {
        $r = eval(<<<PHP
            return new \ReflectionFunction(
              function(int|\DateTimeInterface \$a) {}
            );
PHP
        );
        $args = [
            'date' => $date = new \DateTime(),
            'x' => 1
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([$date], $organised);
    }
}
