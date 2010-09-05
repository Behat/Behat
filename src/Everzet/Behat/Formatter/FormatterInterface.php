<?php

namespace Everzet\Behat\Formatter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;

interface FormatterInterface
{
    public function __construct(Container $container);
    public function registerListeners(EventDispatcher $dispatcher);
}
