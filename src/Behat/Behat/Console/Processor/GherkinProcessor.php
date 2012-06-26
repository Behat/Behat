<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Gherkin\Filter\NameFilter,
    Behat\Gherkin\Filter\TagFilter,
    Behat\Gherkin\Cache\FileCache;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Gherkin processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinProcessor extends Processor
{
    private $container;

    /**
     * Constructs processor.
     *
     * @param ContainerInterface $container Container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command
            ->addOption('--name', null, InputOption::VALUE_REQUIRED,
                "Only execute the feature elements which match\n" .
                "part of the given name or regex."
            )
            ->addOption('--tags', null, InputOption::VALUE_REQUIRED,
                "Only execute the features or scenarios with tags\n" .
                "matching tag filter expression.\n"
            )
            ->addOption('--cache', null, InputOption::VALUE_REQUIRED,
                "Cache parsed features into specified path."
            )
        ;
    }

    /**
     * Processes data from container and console input.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $gherkinParser = $this->container->get('gherkin');

        $name = $input->getOption('name')
             ?: $this->container->getParameter('gherkin.filters.name');
        if ($name) {
            $gherkinParser->addFilter(new NameFilter($name));
        }

        $tags = $input->getOption('tags')
             ?: $this->container->getParameter('gherkin.filters.tags');
        if ($tags) {
            $gherkinParser->addFilter(new TagFilter($tags));
        }

        $path = $input->getOption('cache')
             ?: $this->container->getParameter('behat.options.cache');
        if ($path) {
            $cache = new FileCache($path);
            $this->container->get('gherkin.loader.gherkin_file')->setCache($cache);
        }
    }
}
