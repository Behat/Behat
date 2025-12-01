<?php

declare(strict_types=1);

use Behat\Behat\HelperContainer\ServiceContainer\HelperContainerExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class MyExtension implements Extension
{
    public function getConfigKey(): string
    {
        return 'container_provider';
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
        $definition = new Definition('MyContainer', []);
        $definition->addTag(HelperContainerExtension::HELPER_CONTAINER_TAG);
        $definition->setPublic(true);

        if (method_exists($definition, 'setShared')) {
            $definition->setShared(false); // <- Starting Symfony 2.8
        } else {
            $definition->setScope(ContainerBuilder::SCOPE_PROTOTYPE); // <- Up to Symfony 2.8
        }

        $container->setDefinition('my_extension.container', $definition);
    }
}

return new MyExtension();
