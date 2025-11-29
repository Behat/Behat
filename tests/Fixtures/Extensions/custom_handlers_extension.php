<?php

declare(strict_types=1);

use Behat\Testwork\Call\Handler\Exception\ClassNotFoundHandler;
use Behat\Testwork\Call\Handler\Exception\MethodNotFoundHandler;
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class NonExistentClassPrinter extends ClassNotFoundHandler
{
    public function handleNonExistentClass($class): void
    {
        var_dump($class);
    }
}

class NonExistentMethodPrinter extends MethodNotFoundHandler
{
    public function handleNonExistentMethod($callable): void
    {
        var_dump($callable);
    }
}

class CustomHandlers implements Extension
{
    public function getConfigKey(): string
    {
        return 'custom_handlers';
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $definition = new Definition('NonExistentClassPrinter', []);
        $definition->addTag(CallExtension::EXCEPTION_HANDLER_TAG, ['priority' => 50]);
        $container->setDefinition(CallExtension::EXCEPTION_HANDLER_TAG . '.class_printer', $definition);

        $definition = new Definition('NonExistentMethodPrinter', []);
        $definition->addTag(CallExtension::EXCEPTION_HANDLER_TAG, ['priority' => 55]);
        $container->setDefinition(CallExtension::EXCEPTION_HANDLER_TAG . '.method_printer', $definition);
    }
}

return new CustomHandlers();
