<?php

namespace Behat\Behat\Hook\Annotation;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * SuiteHook hook class.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class SuiteHook extends Hook
{
    /**
     * @see Behat\Behat\Annotation\Annotation::__construct
     */
    public function __construct(array $callback)
    {
        $methodRefl = new \ReflectionMethod($callback[0], $callback[1]);

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Callback should be valid callable');
        }

        if (!$methodRefl->isStatic()) {
            throw new \InvalidArgumentException('Suite hook callbacks should be static methods');
        }

        parent::__construct($callback);
    }
}
