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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class SuiteHook extends Hook
{
    /**
     * {@inheritdoc}
     */
    public function __construct($callback)
    {
        parent::__construct($callback);

        if (!$this->isClosure()) {
            $methodRefl = new \ReflectionMethod($callback[0], $callback[1]);

            if (!$methodRefl->isStatic()) {
                throw new \InvalidArgumentException(sprintf(
                    '"%s" hook callback: %s::%s() must be a static method',
                    basename(str_replace('\\', '/', get_class($this))), $callback[0], $callback[1]
                ));
            }
        }
    }
}
