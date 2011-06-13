<?php

namespace Behat\Behat\Hook\Annotation;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Event\EventInterface,
    Behat\Behat\Hook\HookInterface,
    Behat\Behat\Annotation\Annotation;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base hook class.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Hook extends Annotation implements HookInterface
{
    /**
     * @see     Behat\Behat\Hook\HookInterface::run()
     */
    public function run(EventInterface $event)
    {
        call_user_func($this->getCallback(), $event);
    }
}
