<?php

namespace Everzet\Behat\Collector;

use Symfony\Component\EventDispatcher\EventDispatcher;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Collector interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface CollectorInterface
{
    /**
     * Registers custom listeners on event dispatcher.
     *
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function registerListeners(EventDispatcher $dispatcher);
}
