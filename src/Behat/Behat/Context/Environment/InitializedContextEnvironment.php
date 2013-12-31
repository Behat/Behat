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
use Behat\Behat\Context\Pool\InitializedContextPool;
use Behat\Testwork\Call\Callee;

/**
 * Initialized context environment.
 *
 * Environment based on initialized context pool.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class InitializedContextEnvironment implements ContextEnvironment
{
    /**
     * @var string
     */
    private $suiteName;
    /**
     * @var InitializedContextPool
     */
    private $contextPool;

    /**
     * Initializes environment.
     *
     * @param string                 $suiteName
     * @param InitializedContextPool $contextPool
     */
    public function __construct($suiteName, InitializedContextPool $contextPool)
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
     * @return InitializedContextPool
     */
    public function getContextPool()
    {
        return $this->contextPool;
    }

    /**
     * Creates callable using provided Callee and context pool in hand.
     *
     * @param Callee $callee
     *
     * @return callable
     */
    public function bindCallee(Callee $callee)
    {
        $callable = $callee->getCallable();

        if ($callee->isAnInstanceMethod()) {
            $callable = $callee->getCallable();
            $callable = array($this->contextPool->getContext($callable[0]), $callable[1]);

            return $callable;
        }

        return $callable;
    }
}
