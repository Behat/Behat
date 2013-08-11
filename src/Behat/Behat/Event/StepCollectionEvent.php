<?php

namespace Behat\Behat\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Suite\SuiteInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base scenario event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class StepCollectionEvent extends Event implements LifecycleEventInterface
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
     * @var null|integer
     */
    private $result;

    /**
     * Initializes scenario event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param null|integer         $result
     */
    public function __construct(SuiteInterface $suite, ContextPoolInterface $contexts, $result = null)
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
        $this->result = $result;
    }

    /**
     * Returns suite instance.
     *
     * @return SuiteInterface
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Returns context pool instance.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool()
    {
        return $this->contexts;
    }

    /**
     * Returns scenario tester result code.
     *
     * @return null|integer
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Checks whether scenario was skipped.
     *
     * @return Boolean
     */
    public function isSkipped()
    {
        return StepEvent::SKIPPED === $this->result;
    }
}
