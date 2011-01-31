<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\EventDispatcher\Event;

use Symfony\Component\Finder\Finder;

use Behat\Behat\Formatter\ProgressFormatter,
    Behat\Behat\Formatter\PrettyFormatter;

use Behat\Gherkin\Filter\NameFilter,
    Behat\Gherkin\Filter\TagFilter;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat application test command.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatCommand extends Command
{
    /**
     * @see     Symfony\Component\Console\Command\Command
     */
    protected function configure()
    {
        $this->setName('behat');

        // Set commands default parameters from container loaded ones
        $this->setDefinition(array(
            new InputArgument('features'
              , InputArgument::OPTIONAL
              , 'Features path'
            ),
            new InputOption('--configuration',  '-c'
              , InputOption::VALUE_REQUIRED
              , 'Specify external configuration file to load (*.xml or *.yml).'
            ),
            new InputOption('--format',         '-f'
              , InputOption::VALUE_REQUIRED
              , 'How to format features (Default: pretty). Available formats is pretty, progress, html.'
            ),
            new InputOption('--out',            '-o'
              , InputOption::VALUE_REQUIRED
              , 'Write output to a file/directory instead of STDOUT.'
            ),
            new InputOption('--name',           '-n' 
              , InputOption::VALUE_REQUIRED
              , 'Only execute the feature elements (features or scenarios) which match part of the given name.'
            ),
            new InputOption('--tags',           '-t'
              , InputOption::VALUE_REQUIRED
              , 'Only execute the features or scenarios with tags matching expression.'
            ),
            new InputOption('--lang',           '-l'
              , InputOption::VALUE_REQUIRED
              , 'Print formatters output in particular language.'
            ),
            new InputOption('--verbose',        '-v'
              , InputOption::VALUE_NONE
              , 'Increase verbosity of fail messages.'
            ),
            new InputOption('--no-colors',      '-C'
              , InputOption::VALUE_NONE
              , 'Do not use ANSI color in the output.'
            ),
            new InputOption('--no-time',        '-T'
              , InputOption::VALUE_NONE
              , 'Hide time statistics in output.'
            ),
            new InputOption('--help',           '-h'
              , InputOption::VALUE_NONE
              , 'Display this help message.'
            ),
            new InputOption('--version',        '-V'
              , InputOption::VALUE_NONE
              , 'Display this program version.'
            ),
        ));
    }

    /**
     * @see     Symfony\Component\Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Create container
        $container = $this->createContainer($input->getOption('configuration'));

        // Read command arguments & options into container
        if ($path = $input->getArgument('features')) {
            if (is_file(($path = realpath($path)))) {
                $container->setParameter('behat.paths.base', dirname($path));
                $container->setParameter('behat.paths.features', $path);
            } elseif (is_dir($path)) {
                $container->setParameter('behat.paths.base', $path);
            } elseif (is_dir($path .= '/features')) {
                $container->setParameter('behat.paths.base', $path);
            } else {
                throw new \InvalidArgumentException("Path $path not found");
            }
        }
        if ($name = $input->getOption('name')) {
            $container->setParameter('gherkin.filter.name', $name);
        }
        if ($tags = $input->getOption('tags')) {
            $container->setParameter('gherkin.filter.tags', $tags);
        }
        if ($format = $input->getOption('format')) {
            $container->setParameter('behat.formatter.name', $format);
        }
        if ($out = $input->getOption('out')) {
            $container->setParameter('behat.formatter.output_path', $out);
        }
        if ($noColors = $input->getOption('no-colors')) {
            $container->setParameter('behat.formatter.decorated', !$noColors);
        }
        if ($noTime = $input->getOption('no-time')) {
            $container->setParameter('behat.formatter.time', !$noTime);
        }
        if ($verbose = $input->getOption('verbose')) {
            $container->setParameter('behat.formatter.verbose', $verbose);
        }
        if ($lang = $input->getOption('lang')) {
            $container->setParameter('behat.formatter.language', $lang);
        }

        // Compile container
        $container->compile();

        $eventDispatcher        = $container->get('behat.event_dispatcher');
        $translator             = $container->get('behat.translator');
        $gherkin                = $container->get('gherkin');
        $definitionsContainer   = $container->get('behat.definitions_container');
        $hooksContainer         = $container->get('behat.hooks_container');
        $statisticsCollector    = $container->get('behat.statistics_collector');

        // Configure Gherkin manager filters
        if ($name = $container->getParameter('gherkin.filter.name')) {
            $gherkin->addFilter(new NameFilter($name));
        }
        if ($tags = $container->getParameter('gherkin.filter.tags')) {
            $gherkin->addFilter(new TagFilter($tags));
        }

        // Add definitions files to container resources list
        foreach ((array) $container->getParameter('behat.paths.steps') as $stepsPath) {
            if (is_dir($stepsPath)) {
                foreach ($this->findDefinitionResources($stepsPath) as $path) {
                    $definitionsContainer->addResource('php', $path);
                }
            }
        }

        // Configure hooks container
        foreach ((array) $container->getParameter('behat.paths.hooks') as $path) {
            if (is_file($path)) {
                $hooksContainer->addResource('php', $path);
            }
        }
        $eventDispatcher->registerHooksContainer($hooksContainer);

        // Configure formatter
        switch ($container->getParameter('behat.formatter.name')) {
            case 'progress':
                $formatter = new ProgressFormatter($translator);
                break;
            case 'pretty':
            default:
                $formatter = new PrettyFormatter($translator);
        }
        $formatter->setParameter('verbose', $container->getParameter('behat.formatter.verbose'));
        $formatter->setParameter('decorated', $container->getParameter('behat.formatter.decorated'));
        $formatter->setParameter('language', $container->getParameter('behat.formatter.language'));
        $formatter->setParameter('time', $container->getParameter('behat.formatter.time'));
        $formatter->setParameter('base_path', $container->getParameter('behat.paths.base'));
        $eventDispatcher->registerFormatter($formatter);

        // Register statistics collector on Event Dispatcher
        $eventDispatcher->registerStatisticsCollector($statisticsCollector);

        // Find features paths
        if (is_dir($container->getParameter('behat.paths.features'))) {
            $featurePaths = $this->findFeatureResources($container->getParameter('behat.paths.features'));
        } else {
            $featurePaths = (array) $container->getParameter('behat.paths.features');
        }

        // Notify suite.before event
        $eventDispatcher->notify(new Event($statisticsCollector, 'suite.before'));

        // Run features
        $result = 0;
        foreach ($featurePaths as $path) {
            $features = $gherkin->load((string) $path);
            foreach ($features as $feature) {
                $tester = $container->get('behat.tester.feature');
                $result = max($result, $feature->accept($tester));
            }
        }

        // Notify suite.after event
        $eventDispatcher->notify(new Event($statisticsCollector, 'suite.after'));

        // Return exit code
        return intval(0 < $result);
    }

    /**
     * Create Dependency Injection Container and import external configuration file into it. 
     * 
     * @param   string              $configurationFile  configuration file (may be YAML or XML)
     *
     * @return  ContainerBuilder                        container instance
     */
    protected function createContainer($configurationFile = null)
    {
        $cwd        = getcwd();
        $container  = new ContainerBuilder();
        $xmlLoader  = new XmlFileLoader($container);
        $xmlLoader->load(__DIR__ . '/../../DependencyInjection/config/behat.xml');

        // Guess configuration file path
        if (null !== $configurationFile) {
            $container->setParameter('behat.configuration.path', dirname($configurationFile));
        } elseif (is_file($cwd . '/behat.yml')) {
            $configurationFile = $cwd . '/behat.yml';
            $container->setParameter('behat.configuration.path', $cwd);
        } elseif (is_file($cwd . '/config/behat.yml')) {
            $configurationFile = $cwd . '/config/behat.yml';
            $container->setParameter('behat.configuration.path', $cwd . '/config');
        } elseif (is_file($cwd . '/behat.xml')) {
            $configurationFile = $cwd . '/behat.yml';
            $container->setParameter('behat.configuration.path', $cwd);
        } elseif (is_file($cwd . '/config/behat.xml')) {
            $configurationFile = $cwd . '/config/behat.yml';
            $container->setParameter('behat.configuration.path', $cwd . '/config');
        }

        // Load configuration file with proper loader
        if (null !== $configurationFile) {
            if (false !== mb_stripos($configurationFile, '.xml')) {
                $loader = new XmlFileLoader($container);
            } elseif (false !== mb_stripos($configurationFile, '.yml')) {
                $loader = new YamlFileLoader($container);
            }

            if (!isset($loader)) {
                throw new \InvalidArgumentException(sprintf('Unknown configuration file type given "%s"', $configurationFile));
            }

            $loader->import($configurationFile);
        }

        // Set initial container services & parameters
        $container->setParameter('behat.paths.workdir', $cwd);
        $container->setParameter('behat.paths.lib',  $behatPath = realpath(__DIR__ . '/../../../../../'));
        $container->setParameter('gherkin.paths.lib',  realpath($behatPath . '/vendor/gherkin'));

        return $container;
    }

    /**
     * Find definitions files in specified path. 
     * 
     * @param   string  $stepsPath  steps files path
     * 
     * @return  mixed               files iterator
     */
    protected function findFeatureResources($featuresPath)
    {
        $finder = new Finder();
        $paths  = $finder->files()->name('*.feature')->in($featuresPath);

        return $paths;
    }

    /**
     * Find definitions files in specified path. 
     * 
     * @param   string  $stepsPath  steps files path
     * 
     * @return  mixed               files iterator
     */
    protected function findDefinitionResources($stepsPath)
    {
        $finder = new Finder();
        $paths  = $finder->files()->name('*.php')->in($stepsPath);

        return $paths;
    }
}
