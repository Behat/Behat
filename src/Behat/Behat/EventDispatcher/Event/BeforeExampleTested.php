<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

/**
 * Represents an event before example is tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeExampleTested extends BeforeScenarioTested
{
    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return ExampleTested::BEFORE;
    }
}
