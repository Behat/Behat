<?php

use Behat\Config\ConfigurableExtensionInterface;
use Behat\Config\Extension as ExtensionConfig;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\ServiceContainer\Extension;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigurableExtension implements Extension, ConfigurableExtensionInterface
{
    public function getConfigKey()
    {
        return 'custom_extension';
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder->useAttributeAsKey('name')->prototype('variable');
    }

    public function initialize(Behat\Testwork\ServiceContainer\ExtensionManager $extensionManager)
    {
    }

    public function load(ContainerBuilder $container, array $config)
    {
    }

    public function process(ContainerBuilder $container)
    {
    }

    public function getExtensionConfigObject(string $name, array $settings): ExtensionConfig
    {
        return new ConfigurableExtensionConfig($name, $settings);
    }
}

class ConfigurableExtensionConfig extends ExtensionConfig
{
    public function withProperty(string $value): self
    {
        $this->settings['property'] = $value;
        return $this;
    }

    public function toPhpExpr(): Expr
    {
        $extensionObject =  $this->builderFactory->new(new FullyQualified(self::class));
        $expr = $extensionObject;
        if (isset($this->settings['property'])) {
            $args = $this->builderFactory->args([$this->settings['property']]);
            $expr = $this->builderFactory->methodCall($expr, 'withProperty', $args);
            unset($this->settings['property']);
        }

        if ($this->settings !== []) {
            $args = $this->builderFactory->args([$this->settings]);
            $extensionObject->args = $args;
        }

        return $expr;
    }
}

return new ConfigurableExtension();
