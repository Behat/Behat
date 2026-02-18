<?php

declare(strict_types=1);

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DeprecationExtension implements Extension
{
    public function getConfigKey(): string
    {
        return 'deprecation_extension';
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
        @trigger_error('This extension triggers a deprecation during initialization', E_USER_DEPRECATED);
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        @trigger_error('This extension triggers a deprecation during load', E_USER_DEPRECATED);
    }

    public function process(ContainerBuilder $container): void
    {
    }
}

return new DeprecationExtension();
