<?php

namespace Behat\Testwork\Event;


if (class_exists(\Symfony\Contracts\EventDispatcher\Event::class)) {
    class Event extends \Symfony\Contracts\EventDispatcher\Event
    {
    }
    
} else {
    class Event extends \Symfony\Component\EventDispatcher\Event
    {
    }
}
