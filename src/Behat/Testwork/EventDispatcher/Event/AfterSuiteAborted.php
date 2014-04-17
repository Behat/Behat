<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Specification\NoSpecificationsIterator;

/**
 * Represents an event in which suite was aborted.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterSuiteAborted extends SuiteTested
{
    /**
     * {@inheritdoc}
     */
    public function getSpecificationIterator()
    {
        return new NoSpecificationsIterator($this->getSuite());
    }
}
