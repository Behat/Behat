<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CustomInitializer2 implements ContextInitializer
{
    public function supportsContext(Context $context): bool
    {
        return true;
    }

    public function initializeContext(Context $context): void
    {
        $context->addExtension('custom extension 2');
    }
}

class CustomExtension2 implements Extension
{
    public function getConfigKey(): string
    {
        return 'custom_extension2';
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $definition = $container->register('custom_initializer2', 'CustomInitializer2');
        $definition->addTag('context.initializer', ['priority' => 100]);
    }

    public function process(ContainerBuilder $container): void
    {
    }
}

return new CustomExtension2();
