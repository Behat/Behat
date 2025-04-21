<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output;

use Behat\Config\Formatter\ShowOutputOption;
use Behat\Testwork\Event\Event;
use Behat\Testwork\EventDispatcher\TestworkEventDispatcher;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Behat\Testwork\Output\Printer\OutputPrinter;

/**
 * Formatter built around the idea of event delegation and composition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class NodeEventListeningFormatter implements Formatter
{
    /**
     * @var OutputPrinter
     */
    private $printer;
    /**
     * @var array
     */
    private $parameters;
    /**
     * @var EventListener
     */
    private $listener;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $description;

    /**
     * Initializes formatter.
     *
     * @param string        $name
     * @param string        $description
     */
    public function __construct($name, $description, array $parameters, OutputPrinter $printer, EventListener $listener)
    {
        $this->name = $name;
        $this->description = $description;
        $this->parameters = $parameters;
        $this->printer = $printer;
        $this->listener = $listener;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [TestworkEventDispatcher::BEFORE_ALL_EVENTS => 'listenEvent'];
    }

    /**
     * Proxies event to the listener.
     *
     * @param string|null $eventName
     */
    public function listenEvent(Event $event, $eventName = null)
    {
        if (null === $eventName) {
            if (method_exists($event, 'getName')) {
                $eventName = $event->getName();
            } else {
                $eventName = get_class($event);
            }
        }

        $this->listener->listenEvent($this, $event, $eventName);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getOutputPrinter()
    {
        return $this->printer;
    }

    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    public function getParameter($name)
    {
        $value = $this->parameters[$name] ?? null;
        if ($value !== null && $name === ShowOutputOption::OPTION_NAME) {
            return ShowOutputOption::from($value);
        }

        return $value;
    }
}
