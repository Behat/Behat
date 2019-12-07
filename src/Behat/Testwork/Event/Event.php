<?php

namespace Behat\Testwork\Event;

use Behat\Testwork\EventDispatcher\TestworkEventDispatcher;

if (TestworkEventDispatcher::DISPATCHER_VERSION === 2) {
    class Event extends \Symfony\Contracts\EventDispatcher\Event
    {
    }
    
} else {
    class Event extends \Symfony\Component\EventDispatcher\Event
    {
    }
}
