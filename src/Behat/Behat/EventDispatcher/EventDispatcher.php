<?php

namespace Behat\Behat\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;

use Behat\Behat\Formatter\FormatterInterface,
    Behat\Behat\DataCollector\LoggerDataCollector,
    Behat\Behat\Hook\HookDispatcher;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Event dispatcher.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatcher extends BaseEventDispatcher
{
    /**
     * Registers HookDispatcher event listeners.
     *
     * @param   Behat\Behat\Hook\HookDispatcher     $dispatcher
     *
     * @uses    Behat\Behat\Hook\HookDispatcher::registerListeners()
     */
    public function bindHookDispatcherEventListeners(HookDispatcher $dispatcher)
    {
        $dispatcher->registerListeners($this);
    }

    /**
     * Registers DataLogger event listeners.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger
     *
     * @uses    Behat\Behat\DataCollector\LoggerDataCollector::registerListeners()
     */
    public function bindLoggerEventListeners(LoggerDataCollector $logger)
    {
        $logger->registerListeners($this);
    }

    /**
     * Registers formatter event listeners.
     *
     * @param   Behat\Behat\Formatter\FormatterInterface    $formatter  Behat output formatter
     *
     * @uses    Behat\Behat\Formatter\FormatterInterface::registerListeners()
     */
    public function bindFormatterEventListeners(FormatterInterface $formatter)
    {
        $formatter->registerListeners($this);
    }
}
