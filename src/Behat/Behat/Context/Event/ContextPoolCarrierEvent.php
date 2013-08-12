<?php

namespace Behat\Behat\Context\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Event\LifecycleEventInterface;
use Behat\Behat\Suite\SuiteInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Context pool carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextPoolCarrierEvent extends Event implements LifecycleEventInterface
{
    /**
     * @var SuiteInterface
     */
    private $suite;
    /**
     * @var ContextPoolInterface
     */
    private $contexts;

    /**
     * Initializes event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     */
    public function __construct(SuiteInterface $suite, ContextPoolInterface $contexts = null)
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
    }

    /**
     * Returns suite.
     *
     * @return SuiteInterface
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Sets context pool.
     *
     * @param ContextPoolInterface $contexts
     */
    public function setContextPool(ContextPoolInterface $contexts)
    {
        $this->contexts = $contexts;
    }

    /**
     * Checks whether carrier already has context pool.
     *
     * @return Boolean
     */
    public function hasContextPool()
    {
        return null !== $this->contexts;
    }

    /**
     * Returns context pool.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool()
    {
        return $this->contexts;
    }
}
