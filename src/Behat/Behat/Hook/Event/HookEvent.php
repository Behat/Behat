<?php

namespace Behat\Behat\Hook\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Event\LifecycleEventInterface;
use Behat\Behat\Hook\HookInterface;
use Behat\Behat\Suite\SuiteInterface;
use Exception;
use Symfony\Component\EventDispatcher\Event;

/**
 * Hook event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookEvent extends Event implements LifecycleEventInterface
{
    /**
     * @var LifecycleEventInterface
     */
    private $event;
    /**
     * @var HookInterface
     */
    private $hook;
    /**
     * @var null|string
     */
    private $stdOut;
    /**
     * @var null|string
     */
    private $exception;

    /**
     * Initializes hook event.
     *
     * @param LifecycleEventInterface $event
     * @param HookInterface           $hook
     * @param null|string             $stdOut
     * @param null|Exception          $exception
     */
    public function __construct(
        LifecycleEventInterface $event,
        HookInterface $hook,
        $stdOut = null,
        Exception $exception = null
    )
    {
        $this->event = $event;
        $this->hook = $hook;
        $this->stdOut = $stdOut;
        $this->exception = $exception;
    }

    /**
     * Returns suite instance.
     *
     * @return SuiteInterface
     */
    public function getSuite()
    {
        return $this->event->getSuite();
    }

    /**
     * Returns context pool instance.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool()
    {
        return $this->event->getContextPool();
    }

    /**
     * Returns lifecycle event the hook was caused by.
     *
     * @return LifecycleEventInterface
     */
    public function getLifecycleEvent()
    {
        return $this->event;
    }

    /**
     * Returns hook instance.
     *
     * @return HookInterface
     */
    public function getHook()
    {
        return $this->hook;
    }

    /**
     * Checks if standard output was produced during event.
     *
     * @return Boolean
     */
    public function hasStdOut()
    {
        return null !== $this->stdOut;
    }

    /**
     * Returns standard output produced during event.
     *
     * @return null|string
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }

    /**
     * Checks whether event contains exception.
     *
     * @return Boolean
     */
    public function hasException()
    {
        return null !== $this->exception;
    }

    /**
     * Returns step tester exception.
     *
     * @return null|Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
