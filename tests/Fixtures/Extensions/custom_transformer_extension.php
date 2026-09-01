<?php

declare(strict_types=1);

use Behat\Behat\Transformation\Scope\TransformationScope;
use Behat\Behat\Transformation\ServiceContainer\TransformationExtension;
use Behat\Behat\Transformation\Transformer\ArgumentTransformer;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * A third-party argument transformer, written against nothing but Behat's public API.
 *
 * It rewrites every string argument to include details read back off the
 * TransformationScope, so that the scenario can prove each accessor works.
 */
class ContextReportingTransformer implements ArgumentTransformer
{
    public function supportsDefinitionAndArgument(
        TransformationScope $scope,
        int|string $argumentIndex,
        mixed $argumentValue,
    ): bool {
        return is_string($argumentValue);
    }

    public function transformArgument(
        TransformationScope $scope,
        int|string $argumentIndex,
        mixed $argumentValue,
    ): mixed {
        return sprintf(
            '%s [suite=%s language=%s line=%d pattern=%s]',
            $argumentValue,
            $scope->getEnvironment()->getSuite()->getName(),
            $scope->getFeature()->getLanguage(),
            $scope->getStep()->getLine(),
            $scope->getDefinition()->getPattern(),
        );
    }
}

class CustomTransformer implements Extension
{
    public function getConfigKey(): string
    {
        return 'custom_transformer';
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
        $definition = new Definition('ContextReportingTransformer', []);
        $definition->addTag(TransformationExtension::ARGUMENT_TRANSFORMER_TAG, ['priority' => 100]);
        $container->setDefinition(
            TransformationExtension::ARGUMENT_TRANSFORMER_TAG . '.context_reporting',
            $definition
        );
    }
}

return new CustomTransformer();
