<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

use Behat\Behat\DataCollector\LoggerDataCollector;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Suite event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteEvent extends Event implements EventInterface
{
    private $logger;
    private $completed;
    private $contextParameters;

    /**
     * Initializes suite event.
     *
     * @param LoggerDataCollector $logger            suite logger
     * @param mixed               $contextParameters context parameters
     * @param Boolean             $completed         is suite completed
     */
    public function __construct(LoggerDataCollector $logger, $contextParameters, $completed)
    {
        $this->logger            = $logger;
        $this->contextParameters = $contextParameters;
        $this->completed         = (Boolean) $completed;
    }

    /**
     * Returns suite logger.
     *
     * @return LoggerDataCollector
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Returns context parameters.
     *
     * @return mixed
     */
    public function getContextParameters()
    {
        return $this->contextParameters;
    }

    /**
     * Checks whether test suite was completed entirely.
     *
     * @return Boolean
     */
    public function isCompleted()
    {
        return $this->completed;
    }
}
