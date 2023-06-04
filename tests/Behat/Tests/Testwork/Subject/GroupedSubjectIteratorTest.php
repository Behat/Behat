<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Tests\Testwork\Subject;

use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Specification\NoSpecificationsIterator;
use Behat\Testwork\Specification\SpecificationArrayIterator;
use PHPUnit\Framework\TestCase;

class GroupedSubjectIteratorTest extends TestCase
{
    public function testIterationWithEmptyAtBeginning()
    {
        $suite = $this->prophesize('Behat\Testwork\Suite\Suite')->reveal();

        $iterator = new GroupedSpecificationIterator($suite, [
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, [$this->prophesize()->reveal()]),
        ]);

        $this->assertEquals(1, iterator_count($iterator));
    }

    public function testIterationWithEmptyInMiddle()
    {
        $suite = $this->prophesize('Behat\Testwork\Suite\Suite')->reveal();

        $iterator = new GroupedSpecificationIterator($suite, [
            new SpecificationArrayIterator($suite, [$this->prophesize()->reveal()]),
            new NoSpecificationsIterator($suite),
            new SpecificationArrayIterator($suite, [$this->prophesize()->reveal()]),
        ]);

        $this->assertEquals(2, iterator_count($iterator));
    }
}
