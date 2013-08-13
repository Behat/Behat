<?php

namespace Behat\Behat\Callee\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\CalleeInterface;
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Event\LifecycleEventInterface;
use Behat\Behat\Suite\SuiteInterface;
use Exception;
use Symfony\Component\EventDispatcher\Event;

/**
 * Callee execution event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExecuteCalleeEvent extends Event implements LifecycleEventInterface
{
    private $suite;
    private $contexts;
    private $callee;
    private $arguments;
    private $executed = false;
    private $return;
    private $exception;

    /**
     * Initializes execution callee event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param CalleeInterface      $callee
     * @param array                $arguments
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        CalleeInterface $callee,
        array $arguments
    )
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
        $this->callee = $callee;
        $this->arguments = $arguments;
    }

    /**
     * Returns associated suite instance.
     *
     * @return SuiteInterface
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Returns associated context pool instance.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool()
    {
        return $this->contexts;
    }

    /**
     * Returns associated callee instance.
     *
     * @return CalleeInterface
     */
    public function getCallee()
    {
        return $this->callee;
    }

    /**
     * Checks if callee has arguments with which it should be called.
     *
     * @return Boolean
     */
    public function hasArguments()
    {
        return 0 !== count($this->arguments);
    }

    /**
     * Returns list of arguments with which callee should be called.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Sets arguments with which callee should be called.
     *
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Checks if callee already has been executed.
     *
     * @return Boolean
     */
    public function isExecuted()
    {
        return $this->executed;
    }

    /**
     * Returns return value of callee execution (if has some).
     *
     * @return null|mixed
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * Sets return value of the callee execution.
     *
     * @param null|mixed $return
     */
    public function setReturn($return)
    {
        $this->return = $return;
        $this->executed = true;
    }

    /**
     * Returns exception which callee thrown (if some).
     *
     * @return null|Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Sets exception which callee thrown (if some).
     *
     * @param Exception $exception
     */
    public function setException(Exception $exception)
    {
        $this->exception = $exception;
        $this->executed = true;
    }
}
