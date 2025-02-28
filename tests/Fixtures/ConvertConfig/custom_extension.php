<?php

use Behat\Behat\Output\Printer\ConsoleOutputFactory;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Output\Printer\StreamOutputPrinter;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CustomExtension implements Extension
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
        $outputPrinterDefinition = new Definition(StreamOutputPrinter::class, array(
            new Definition(ConsoleOutputFactory::class),
        ));
        $container->setDefinition(StreamOutputPrinter::class, $outputPrinterDefinition);

        $formatterDefinition = $container->register(CustomFormatter::class, CustomFormatter::class);
        $formatterDefinition->addArgument(new Reference(StreamOutputPrinter::class));
        $formatterDefinition->addTag(OutputExtension::FORMATTER_TAG);
    }

    public function process(ContainerBuilder $container)
    {
    }
}

class CustomFormatter implements Formatter
{
    public function __construct(
        private OutputPrinter $outputPrinter
    ) {
    }

    public static function getSubscribedEvents()
    {
    }

    public function getName()
    {
        return 'custom_formatter';
    }

    public function getDescription()
    {
    }

    public function getOutputPrinter()
    {
        return $this->outputPrinter;
    }

    public function setParameter($name, $value)
    {
    }

    public function getParameter($name)
    {
    }
}

return new CustomExtension();
