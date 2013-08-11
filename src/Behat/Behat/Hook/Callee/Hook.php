<?php

namespace Behat\Behat\Hook\Callee;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\Callee;
use Behat\Behat\Hook\HookInterface;

/**
 * Base Hook hook class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Hook extends Callee implements HookInterface
{
    /**
     * @var string
     */
    private $eventName;

    /**
     * Initializes hook.
     *
     * @param string      $eventName
     * @param Callable    $callback
     * @param null|string $description
     */
    public function __construct($eventName, $callback, $description = null)
    {
        $this->eventName = $eventName;

        parent::__construct($callback, $description);
    }

    /**
     * Returns hooked event name.
     *
     * @return string
     */
    final public function getEventName()
    {
        return $this->eventName;
    }
}
