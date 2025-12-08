<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\SpecificationIterator;

/**
 * Represents an event in which suite is prepared to be tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeSuiteTested extends SuiteTested implements BeforeTested
{
    /**
     * Initializes event.
     *
     * @param SpecificationIterator<mixed> $iterator
     */
    public function __construct(
        Environment $env,
        private readonly SpecificationIterator $iterator,
    ) {
        parent::__construct($env);
    }

    public function getSpecificationIterator(): SpecificationIterator
    {
        return $this->iterator;
    }
}
