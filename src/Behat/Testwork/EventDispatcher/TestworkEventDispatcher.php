<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Extends Symfony2 event dispatcher with catch-all listeners.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
$identifyEventDispatcherClassVersion = function() {
    $reflection  = new \ReflectionClass(\Symfony\Component\EventDispatcher\EventDispatcher::class);
    $dispatch    = $reflection->getMethod('dispatch');

    if ($dispatch->getNumberOfParameters() === 1) {
        // This is the 4.3 / 4.4 version, which has `public function dispatch($event/*, string $eventName = null*/)` and
        // internally uses func_get_args to work parameters it got in what order. The legacy Testwork class can still
        // extend this because its signature only adds an extra optional param. It may however produce unexpected
        // results as it assumes all dispatch calls are with the legacy sequence of $eventName, $event.
        return TestworkEventDispatcherSymfonyLegacy::class;
    }

    $first_param = $dispatch->getParameters()[0];
    switch ($first_param->getName()) {
        case 'event':
            // This is the new Symfony 5 event dispatcher interface
            // public function dispatch(object $event, string $eventName = null): object
            return TestworkEventDispatcherSymfony5::class;

        case 'eventName':
            // This is the Symfony <= 4.2 version
            // public function dispatch($eventName, Event $event = null)
            return TestworkEventDispatcherSymfonyLegacy::class;

        default:
            throw new \UnexpectedValueException('Could not identify which version of symfony/event-dispatcher is in use, could not define a compatible TestworkEventDispatcher');
    }
};

class_alias($identifyEventDispatcherClassVersion(), \Behat\Testwork\EventDispatcher\TestworkEventDispatcher::class);
unset($identifyEventDispatcherClassVersion);


if (false) {

    // Empty, never-actually-defined, class definition to fool any tooling looking for a class in this file
    final class TestworkEventDispatcher
    {

        // These constant definitions are required to prevent scrutinizer failing static analysis
        const BEFORE_ALL_EVENTS = '*~';
        const AFTER_ALL_EVENTS = '~*';
        const DISPATCHER_VERSION = 'undefined';
    }

}
