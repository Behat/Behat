<?php

namespace Behat\Tests\Testwork\Subject;

use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Specification\NoSpecificationsIterator;
use Behat\Testwork\Specification\SpecificationArrayIterator;
use PHPUnit\Framework\TestCase;

class GroupedSubjectIteratorTest extends TestCase
{
    public function testIterationWithEmptyAtBeginning()
    {
        $suite = $this->createStub('Behat\Testwork\Suite\Suite');
        $subIterator = $this->createStub('Behat\Testwork\Specification\SpecificationIterator');

        $iterator = new GroupedSpecificationIterator($suite, array(
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, array($subIterator)),
        ));

        $this->assertEquals(1, iterator_count($iterator));
    }

    public function testIterationWithEmptyInMiddle()
    {
        $suite = $this->createStub('Behat\Testwork\Suite\Suite');
        $subIterator = $this->createStub('Behat\Testwork\Specification\SpecificationIterator');

        $iterator = new GroupedSpecificationIterator($suite, array(
            new SpecificationArrayIterator($suite, array($subIterator)),
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, array($subIterator)),
        ));

        $this->assertEquals(2, iterator_count($iterator));
    }
}
