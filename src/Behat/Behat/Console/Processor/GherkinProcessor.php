<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Gherkin\Filter\NameFilter,
    Behat\Gherkin\Filter\TagFilter;

class GherkinProcessor implements ProcessorInterface
{
    /**
     * Default Behat formatters.
     *
     * @var     array
     */
    private $defaultFormatters = array(
        'pretty'    => 'Behat\Behat\Formatter\PrettyFormatter',
        'progress'  => 'Behat\Behat\Formatter\ProgressFormatter',
        'html'      => 'Behat\Behat\Formatter\HtmlFormatter',
        'junit'     => 'Behat\Behat\Formatter\JUnitFormatter'
    );

    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::getInputOptions()
     */
    public function getInputOptions()
    {
        return array(
            new InputOption('--name',           null,
                InputOption::VALUE_REQUIRED,
                '         ' .
                'Only execute the feature elements (features or scenarios) which match part of the given name or regex.'
            ),
            new InputOption('--tags',           null,
                InputOption::VALUE_REQUIRED,
                '         ' .
                'Only execute the features or scenarios with tags matching tag filter expression.'."\n"
            ),
        );
    }

    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::process()
     */
    public function process(ContainerInterface $container, InputInterface $input, OutputInterface $output)
    {
        $gherkinParser = $container->get('gherkin');
        if ($name = ($input->getOption('name') ?: $container->getParameter('gherkin.filters.name'))) {
            $gherkinParser->addFilter(new NameFilter($name));
        }
        if ($tags = ($input->getOption('tags') ?: $container->getParameter('gherkin.filters.tags'))) {
            $gherkinParser->addFilter(new TagFilter($tags));
        }
    }
}
