<?php

namespace Behat\Behat\Tester\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Tester\Event\TesterCarrierEvent;
use Behat\Behat\Suite\SuiteInterface;

/**
 * In-suite tester carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class InSuiteTesterCarrierEvent extends TesterCarrierEvent
{
    /**
     * @var SuiteInterface
     */
    private $suite;

    /**
     * Initializes event.
     *
     * @param SuiteInterface $suite
     */
    public function __construct(SuiteInterface $suite)
    {
        $this->suite = $suite;
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
}
