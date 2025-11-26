<?php

use Psr\Container\ContainerInterface;

class Service1
{
    public $state;
}

class Service2
{
    public $state;
    public $myFlag;
}

class Service3
{
    public $state;
}

class Service4
{
    public $state;
}

class ServiceContainer implements ContainerInterface
{
    private $services = [];

    public function has(string $class): bool
    {
        return in_array($class, ['Service1', 'Service2', 'Service3']);
    }

    public function get(string $class)
    {
        if (!$this->has($class)) {
            throw new Behat\Behat\HelperContainer\Exception\ServiceNotFoundException("Service $class not found", $class);
        }

        return isset($this->services[$class])
             ? $this->services[$class]
             : $this->services[$class] = new $class();
    }
}
