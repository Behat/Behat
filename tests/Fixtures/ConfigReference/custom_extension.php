<?php

declare(strict_types=1);

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CustomExtension implements Extension
{
    public function getConfigKey(): string
    {
        return 'custom_extension';
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('child')->info('A child node')->end()
                ->booleanNode('test')->defaultTrue()->end()
            ->end();
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
    }
}

return new CustomExtension();
