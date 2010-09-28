<?php

namespace Everzet\Behat\Filter;

use Symfony\Component\EventDispatcher\EventDispatcher;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Filter Interface.
 *
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface FilterInterface
{
    /**
     * Register listeners in filter.
     *
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function registerListeners(EventDispatcher $dispatcher);
}
