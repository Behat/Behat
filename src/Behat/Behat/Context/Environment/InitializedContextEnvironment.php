<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\Handler\ContextEnvironmentHandler;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Testwork\Call\Callee;
use Behat\Testwork\Suite\Suite;

/**
 * Context environment based on a list of instantiated context objects.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class InitializedContextEnvironment implements ContextEnvironment
{
    /**
     * @var string
     */
    private $suite;
    /**
     * @var Context[]
     */
    private $contexts = array();

    /**
     * Initializes environment.
     *
     * @param Suite $suite
     */
    public function __construct(Suite $suite)
    {
        $this->suite = $suite;
    }

    /**
     * Registers context instance in the environment.
     *
     * @param Context $context
     */
    public function registerContext(Context $context)
    {
        $this->contexts[get_class($context)] = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * {@inheritdoc}
     */
    public function hasContexts()
    {
        return count($this->contexts) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getContextClasses()
    {
        return array_keys($this->contexts);
    }

    /**
     * {@inheritdoc}
     */
    public function hasContextClass($class)
    {
        return isset($this->contexts[$class]);
    }

    /**
     * Returns list of registered context instances.
     *
     * @return Context[]
     */
    public function getContexts()
    {
        return array_values($this->contexts);
    }

    /**
     * Returns registered context by its class name.
     *
     * @param string $class
     *
     * @return Context
     *
     * @throws ContextNotFoundException If context is not in the environment
     */
    public function getContext($class)
    {
        if (!$this->hasContextClass($class)) {
            throw new ContextNotFoundException(sprintf(
                '`%s` context is not found in the suite environment. Have you registered it?',
                $class
            ), $class);
        }

        return $this->contexts[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function bindCallee(Callee $callee)
    {
        $callable = $callee->getCallable();

        if ($callee->isAnInstanceMethod()) {
            return array($this->getContext($callable[0]), $callable[1]);
        }

        return $callable;
    }
}
