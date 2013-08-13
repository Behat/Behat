<?php

namespace Behat\Behat\Hook\Callee;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use InvalidArgumentException;

/**
 * Base SuiteHook hook class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class SuiteHook extends Hook
{
    /**
     * Initializes hook.
     *
     * @param string      $eventName
     * @param Callable    $callback
     * @param null|string $description
     *
     * @throws InvalidArgumentException If callback is a method, but not a static one
     */
    public function __construct($eventName, $callback, $description = null)
    {
        parent::__construct($eventName, $callback, $description);

        if ($this->isMethod()) {
            $reflection = $this->getReflection();

            if (!$reflection->isStatic()) {
                throw new InvalidArgumentException(sprintf(
                    'Suite hook callback: %s::%s() must be a static method',
                    $callback[0],
                    $callback[1]
                ));
            }
        }
    }
}
