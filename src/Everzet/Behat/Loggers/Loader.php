<?php

namespace Everzet\Behat\Loggers;

use \Symfony\Components\DependencyInjection\Container;

interface Loader
{
    public function load(Container $container);
}
