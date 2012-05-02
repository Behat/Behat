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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Hook extends Annotation implements HookInterface
{
    /**
     * Constructs annotation.
     *
     * @param callback $callback
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" hook callback should be a valid callable, but %s given',
                basename(str_replace('\\', '/', get_class($this))), gettype($callback)
            ));
        }
        parent::__construct($callback);
    }

    /**
     * Runs hook callback.
     *
     * @param EventInterface $event
     */
    public function run(EventInterface $event)
    {
        call_user_func($this->getCallback(), $event);
    }
}
