<?php

namespace Behat\Tests\Testwork\Subject;

use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Specification\NoSpecificationsIterator;
use Behat\Testwork\Specification\SpecificationArrayIterator;
use PHPUnit\Framework\TestCase;
use Behat\Testwork\Suite\Suite;

class GroupedSubjectIteratorTest extends TestCase
{
    public function testIterationWithEmptyAtBeginning(): void
    {
        $suite = $this->prophesize(Suite::class)->reveal();

        $iterator = new GroupedSpecificationIterator($suite, array(
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, array($this->prophesize()->reveal())),
        ));

        $this->assertEquals(1, iterator_count($iterator));
    }

    public function testIterationWithEmptyInMiddle(): void
    {
        $suite = $this->prophesize(Suite::class)->reveal();

        $iterator = new GroupedSpecificationIterator($suite, array(
            new SpecificationArrayIterator($suite, array($this->prophesize()->reveal())),
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, array($this->prophesize()->reveal())),
        ));

        $this->assertEquals(2, iterator_count($iterator));
    }
}
