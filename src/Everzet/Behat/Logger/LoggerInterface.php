<?php

namespace Everzet\Behat\Logger;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;

interface LoggerInterface
{
    public function __construct(Container $container);
    public function registerListeners(EventDispatcher $dispatcher);
}
