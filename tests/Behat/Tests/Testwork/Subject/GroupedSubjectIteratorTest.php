<?php

namespace Behat\Tests\Testwork\Subject;

use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Specification\NoSpecificationsIterator;
use Behat\Testwork\Specification\SpecificationArrayIterator;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\Suite;
use PHPUnit\Framework\TestCase;

class GroupedSubjectIteratorTest extends TestCase
{
    public function testIterationWithEmptyAtBeginning(): void
    {
        $suite = $this->createStub(Suite::class);
        $subIterator = $this->createStub(SpecificationIterator::class);

        $iterator = new GroupedSpecificationIterator($suite, [
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, [$subIterator]),
        ]);

        $this->assertEquals(1, iterator_count($iterator));
    }

    public function testIterationWithEmptyInMiddle(): void
    {
        $suite = $this->createStub(Suite::class);
        $subIterator = $this->createStub(SpecificationIterator::class);

        $iterator = new GroupedSpecificationIterator($suite, [
            new SpecificationArrayIterator($suite, [$subIterator]),
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, [$subIterator]),
        ]);

        $this->assertEquals(2, iterator_count($iterator));
    }
}
