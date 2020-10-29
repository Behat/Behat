<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Tests\Testwork\EventDispatcher;

use Behat\Testwork\EventDispatcher\TestworkEventDispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class TestworkEventDispatcherTest extends TestCase
{
    public function testDispatchLegacyCall(): void
    {
        $dispatcher = new TestworkEventDispatcher();
        $event = new class extends Event {};
        $eventName = 'TEST_EVENT';
        $listener = $this->createListenerSpy();

        $dispatcher->addListener($eventName, $listener);
        $dispatcher->dispatch($eventName, $event);

        $this->assertCount(1, $listener->receivedEvents);
        $this->assertEquals($event, $listener->receivedEvents[0]);
    }

    public function testDispatchCurrentCall(): void
    {
        $dispatcher = new TestworkEventDispatcher();
        $event = new class extends Event {};
        $listener = $this->createListenerSpy();

        $dispatcher->addListener(get_class($event), $listener);
        $dispatcher->dispatch($event);

        $this->assertCount(1, $listener->receivedEvents);
        $this->assertEquals($event, $listener->receivedEvents[0]);
    }

    public function testSetNameOnEvent(): void
    {
        $dispatcher = new TestworkEventDispatcher();
        $event = new class extends Event {
            public $name;
            public function setName($name): void
            {
                $this->name = $name;
            }
        };
        $eventName = 'TEST_EVENT';
        $listener = $this->createListenerSpy();

        $dispatcher->addListener($eventName, $listener);
        $dispatcher->dispatch($eventName, $event);

        $this->assertCount(1, $listener->receivedEvents);
        $this->assertEquals($eventName, $event->name);
    }

    public function testBeforeAllListener(): void
    {
        $dispatcher = new TestworkEventDispatcher();
        $event = new class extends Event {};
        $listener = $this->createListenerSpy();

        $dispatcher->addListener(TestworkEventDispatcher::BEFORE_ALL_EVENTS, $listener);
        $dispatcher->dispatch($event);

        $this->assertCount(1, $listener->receivedEvents);
        $this->assertEquals($event, $listener->receivedEvents[0]);
    }

    public function testAfterAllListener(): void
    {
        $dispatcher = new TestworkEventDispatcher();
        $event = new class extends Event {};
        $listener = $this->createListenerSpy();

        $dispatcher->addListener(TestworkEventDispatcher::AFTER_ALL_EVENTS, $listener);
        $dispatcher->dispatch($event);

        $this->assertCount(1, $listener->receivedEvents);
        $this->assertEquals($event, $listener->receivedEvents[0]);
    }

    /**
     * @return callable
     */
    public function createListenerSpy()
    {
        return new class() {
            public $receivedEvents = [];

            public function __invoke(Event $event)
            {
                $this->receivedEvents[] = $event;
            }
        };
    }
}
