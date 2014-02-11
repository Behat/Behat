<?php

namespace Behat\Tests\Testwork\Subject;

use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Specification\NoSpecificationsIterator;
use Behat\Testwork\Specification\SpecificationArrayIterator;
use Prophecy\PhpUnit\ProphecyTestCase;

class GroupedSubjectIteratorTest extends ProphecyTestCase
{
    public function testIterationWithEmptyAtBeginning()
    {
        $suite = $this->prophesize('Behat\Testwork\Suite\Suite')->reveal();

        $iterator = new GroupedSpecificationIterator($suite, array(
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, array($this->prophesize()->reveal())),
        ));

        $this->assertEquals(1, iterator_count($iterator));
    }

    public function testIterationWithEmptyInMiddle()
    {
        $suite = $this->prophesize('Behat\Testwork\Suite\Suite')->reveal();

        $iterator = new GroupedSpecificationIterator($suite, array(
            new SpecificationArrayIterator($suite, array($this->prophesize()->reveal())),
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, array($this->prophesize()->reveal())),
        ));

        $this->assertEquals(2, iterator_count($iterator));
    }
}
