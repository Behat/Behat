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
 * Suite event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteEvent extends Event implements LifecycleEventInterface
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
    private $status;

    /**
     * Initializes suite event.
     *
     * @param SuiteInterface       $suite suite that being started
     * @param ContextPoolInterface $contexts
     * @param null|integer         $status
     */
    public function __construct(SuiteInterface $suite, ContextPoolInterface $contexts, $status = null)
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
        $this->status = $status;
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
     * Returns suite tester status code.
     *
     * @return null|integer
     */
    public function getStatus()
    {
        return $this->status;
    }
}
