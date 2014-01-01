<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Call;

use Behat\Testwork\Call\RuntimeCallee;
use Behat\Testwork\Hook\Hook;

/**
 * Testwork runtime hook.
 *
 * Hook created and executed during runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeHook extends RuntimeCallee implements Hook
{
    /**
     * @var string
     */
    private $eventName;

    /**
     * Initializes hook.
     *
     * @param string      $eventName
     * @param callable    $callable
     * @param null|string $description
     */
    public function __construct($eventName, $callable, $description = null)
    {
        $this->eventName = $eventName;

        parent::__construct($callable, $description);
    }

    /**
     * Returns hooked event name.
     *
     * @return string
     */
    final public function getHookedEventName()
    {
        return $this->eventName;
    }

    /**
     * Represents hook as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
