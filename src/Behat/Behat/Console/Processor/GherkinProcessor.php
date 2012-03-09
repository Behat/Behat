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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinProcessor implements ProcessorInterface
{
    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::confiugre()
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

        if ($path = ($input->getOption('cache') ?: $container->getParameter('behat.options.cache'))) {
            $cache = new FileCache($path);
            $container->get('gherkin.loader.gherkin')->setCache($cache);
        }
    }
}
