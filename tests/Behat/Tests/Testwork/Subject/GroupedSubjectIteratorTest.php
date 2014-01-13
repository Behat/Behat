<?php

namespace Behat\Tests\Testwork\Subject;

use Behat\Testwork\Subject\ArraySubjectIterator;
use Behat\Testwork\Subject\EmptySubjectIterator;
use Behat\Testwork\Subject\GroupedSubjectIterator;
use Prophecy\PhpUnit\ProphecyTestCase;

class GroupedSubjectIteratorTest extends ProphecyTestCase
{
    public function testIterationWithEmptyAtBeginning()
    {
        $suite = $this->prophesize('Behat\Testwork\Suite\Suite')->reveal();

        $iterator = new GroupedSubjectIterator($suite, array(
            new EmptySubjectIterator($suite),
            new ArraySubjectIterator($suite, array($this->prophesize()->reveal())),
        ));

        $this->assertEquals(1, iterator_count($iterator));
    }

    public function testIterationWithEmptyInMiddle()
    {
        $suite = $this->prophesize('Behat\Testwork\Suite\Suite')->reveal();

        $iterator = new GroupedSubjectIterator($suite, array(
            new ArraySubjectIterator($suite, array($this->prophesize()->reveal())),
            new EmptySubjectIterator($suite),
            new ArraySubjectIterator($suite, array($this->prophesize()->reveal())),
        ));

        $this->assertEquals(2, iterator_count($iterator));
    }
}
