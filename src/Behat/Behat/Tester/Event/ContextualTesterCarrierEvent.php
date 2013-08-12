<?php

namespace Behat\Behat\Tester\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Tester\Event\InSuiteTesterCarrierEvent;
use Behat\Behat\Event\LifecycleEventInterface;
use Behat\Behat\Suite\SuiteInterface;

/**
 * Contextual tester carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class ContextualTesterCarrierEvent extends InSuiteTesterCarrierEvent implements LifecycleEventInterface
{
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
    public function __construct(SuiteInterface $suite, ContextPoolInterface $contexts)
    {
        parent::__construct($suite);

        $this->contexts;
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
