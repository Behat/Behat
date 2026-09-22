<?php

namespace Behat\PHPUnitAssertionsExtension;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class BehatPHPUnitAssertionsExtension implements Extension
{
    public function getConfigKey(): string
    {
        return 'phpunit_assertions';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
        // No-op
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        // No-op
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        // No-op
    }

    public function process(ContainerBuilder $container): void
    {
        // No-op
    }
}

return new BehatPHPUnitAssertionsExtension();
