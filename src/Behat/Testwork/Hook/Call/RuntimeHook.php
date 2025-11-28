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
use Stringable;

/**
 * Represents a hook executed during the execution runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeHook extends RuntimeCallee implements Stringable, Hook
{
    /**
     * Initializes hook.
     *
     * @param string      $scopeName
     * @param callable    $callable
     * @param string|null $description
     */
    public function __construct(
        private $scopeName,
        $callable,
        $description = null,
    ) {
        parent::__construct($callable, $description);
    }

    public function getScopeName()
    {
        return $this->scopeName;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
