<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CustomInitializer implements ContextInitializer
{
    private array $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function supportsContext(Context $context): bool
    {
        return true;
    }

    public function initializeContext(Context $context): void
    {
        $context->setExtensionParameters($this->parameters);
    }
}

class CustomExtension implements Extension
{
    public function getConfigKey(): string
    {
        return 'custom';
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder->useAttributeAsKey('name')->prototype('variable');
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $definition = $container->register('custom_initializer', 'CustomInitializer');
        $definition->setArguments([$config]);
        $definition->addTag('context.initializer', ['priority' => 100]);
    }

    public function process(ContainerBuilder $container): void
    {
    }
}

return new CustomExtension();
