<?php

namespace Behat\Tests\Testwork\Subject;

use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Specification\NoSpecificationsIterator;
use Behat\Testwork\Specification\SpecificationArrayIterator;
use PHPUnit\Framework\TestCase;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Specification\SpecificationIterator;

class GroupedSubjectIteratorTest extends TestCase
{
    public function testIterationWithEmptyAtBeginning(): void
    {
        $suite = $this->createStub(Suite::class);
        $subIterator = $this->createStub(SpecificationIterator::class);

        $iterator = new GroupedSpecificationIterator($suite, array(
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, array($subIterator)),
        ));

        $this->assertEquals(1, iterator_count($iterator));
    }

    public function testIterationWithEmptyInMiddle(): void
    {
        $suite = $this->createStub(Suite::class);
        $subIterator = $this->createStub(SpecificationIterator::class);

        $iterator = new GroupedSpecificationIterator($suite, array(
            new SpecificationArrayIterator($suite, array($subIterator)),
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, array($subIterator)),
        ));

        $this->assertEquals(2, iterator_count($iterator));
    }
}
