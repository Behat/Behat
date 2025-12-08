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
     * Initializes formatter.
     *
     * @param string        $name
     * @param string        $description
     */
    public function __construct(
        private $name,
        private $description,
        private array $parameters,
        private readonly OutputPrinter $printer,
        private readonly EventListener $listener,
    ) {
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [TestworkEventDispatcher::BEFORE_ALL_EVENTS => 'listenEvent'];
    }

    /**
     * Proxies event to the listener.
     *
     * @param string|null $eventName
     */
    public function listenEvent(Event $event, $eventName = null): void
    {
        if (null === $eventName) {
            $eventName = method_exists($event, 'getName') ? $event->getName() : $event::class;
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

    public function getOutputPrinter(): OutputPrinter
    {
        return $this->printer;
    }

    public function setParameter($name, $value): void
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
