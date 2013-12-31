<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment;

use Behat\Behat\Context\Environment\Handler\ContextEnvironmentHandler;
use Behat\Behat\Context\Pool\UninitializedContextPool;
use Behat\Testwork\Environment\StaticEnvironment;

/**
 * Uninitialized context environment.
 *
 * Environment based on uninitialized context pool.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class UninitializedContextEnvironment extends StaticEnvironment implements ContextEnvironment
{
    /**
     * @var string
     */
    private $suiteName;
    /**
     * @var UninitializedContextPool
     */
    private $contextPool;

    /**
     * Initializes environment.
     *
     * @param string                   $suiteName
     * @param UninitializedContextPool $contextPool
     */
    public function __construct($suiteName, UninitializedContextPool $contextPool)
    {
        $this->suiteName = $suiteName;
        $this->contextPool = $contextPool;
    }

    /**
     * Returns unique ID used for reading and caching of environment assets (callees).
     *
     * @return string
     */
    public function getSuiteName()
    {
        return $this->suiteName;
    }

    /**
     * Returns context pool.
     *
     * @return UninitializedContextPool
     */
    public function getContextPool()
    {
        return $this->contextPool;
    }
}
