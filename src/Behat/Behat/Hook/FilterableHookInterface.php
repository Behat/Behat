<?php

namespace Behat\Behat\Hook;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;

/**
 * Filterable hook interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface FilterableHookInterface extends HookInterface
{
    /**
     * Checks that current hook matches provided event object.
     *
     * @param EventInterface $event
     *
     * @return Boolean
     */
    public function filterMatches(EventInterface $event);
}
