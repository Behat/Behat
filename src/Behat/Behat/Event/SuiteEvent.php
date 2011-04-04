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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteEvent extends Event implements EventInterface
{
    private $logger;

    /**
     * Initializes suite event.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger
     */
    public function __construct(LoggerDataCollector $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns suite logger.
     *
     * @return  Behat\Behat\DataCollector\LoggerDataCollector
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
