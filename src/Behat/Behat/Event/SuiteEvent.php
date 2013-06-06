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
class SuiteEvent extends BehatEvent
{
    private $logger;
    private $completed;
    private $parameters;

    /**
     * Initializes suite event.
     *
     * @param LoggerDataCollector $logger     suite logger
     * @param mixed               $parameters context parameters
     * @param Boolean             $completed  is suite completed
     */
    public function __construct(LoggerDataCollector $logger, $parameters, $completed)
    {
        $this->logger     = $logger;
        $this->parameters = $parameters;
        $this->completed  = (Boolean) $completed;
    }

    /**
     * Serialize class properties.
     * @return string
     */
    public function serialize()
    {
        return serialize(
            array(
                'logger' => $this->logger,
                'parameters' => $this->parameters,
                'completed' => $this->completed,
                'parentData' => parent::serialize(),
            )
        );
    }

    /**
     * Unserialize class properties.
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->logger = $data['logger'];
        $this->parameters = $data['parameters'];
        $this->completed = $data['completed'];
        parent::unserialize($data['parentData']);
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
        return $this->parameters;
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
