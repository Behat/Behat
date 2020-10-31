<?php

namespace Behat\Tests\Testwork\Argument;

use Behat\Testwork\Argument\MixedArgumentOrganiser;
use PHPUnit\Framework\TestCase;

final class MixedArgumentOrganiserTest extends TestCase
{
    private $organiser;

    function setUp() : void
    {
        $this->organiser = new MixedArgumentOrganiser();
    }

    /** @test */
    function it_organises_nothing_if_no_args()
    {
        $r = new \ReflectionFunction(
            function(\DateTimeInterface $d) {}
        );
        $args = [];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([], $organised);
    }

    /** @test */
    function it_matches_args_by_position()
    {
        $r = new \ReflectionFunction(
            function($x, $y) {}
        );
        $args = [
            1,
            2,
            3
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([1,2], $organised);
    }

    /** @test */
    function it_matches_args_by_name()
    {
        $r = new \ReflectionFunction(
            function($date) {}
        );
        $args = [
            'date' => $date = new \DateTime(),
            'x' => new \stdClass()
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame(['date' => $date], $organised);
    }

    /** @test */
    function it_matches_args_by_type()
    {
        $r = new \ReflectionFunction(
            function(\DateTimeInterface $d) {}
        );
        $args = [
            'x' => $date = new \DateTime(),
            'y' => new \stdClass()
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([$date], $organised);
    }

    /** @test */
    function it_matches_args_by_name_over_type()
    {
        $r = new \ReflectionFunction(
            function(\DateTimeInterface $a, $date) {}
        );
        $args = [
            'date' => $date = new \DateTime(),
            'x' => 1
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([1, 'date' => $date], $organised);
    }

    /**
     * @test
     * @requires PHP >= 8.0
     */
    function it_matches_union_types()
    {
        $r = eval(<<<CODE
            return new \ReflectionFunction(
              function(int|\DateTimeInterface \$a) {}
            );
CODE
        );
        $args = [
            'date' => $date = new \DateTime(),
            'x' => 1
        ];

        $organised = $this->organiser->organiseArguments($r, $args);

        $this->assertSame([$date], $organised);
    }
}
