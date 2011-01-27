<?php

namespace Everzet\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\EventDispatcher\Event;

use Symfony\Component\Finder\Finder;

use Behat\Gherkin\Filter\NameFilter,
    Behat\Gherkin\Filter\TagFilter;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
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
            new InputOption('--i18n',           '-i'
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
        if (null !== $input->getArgument('features')) {
            $container->setParameter('behat.features.path',     realpath($input->getArgument('features')));
        }
        if (null !== $input->getOption('name')) {
            $container->setParameter('gherkin.filter.name',     $input->getOption('name'));
        }
        if (null !== $input->getOption('tags')) {
            $container->setParameter('gherkin.filter.tags',     $input->getOption('tags'));
        }
        if (null !== $input->getOption('format')) {
            $container->setParameter('behat.formatter.name',    $input->getOption('format'));
        }
        if (null !== $input->getOption('out')) {
            $container->setParameter('behat.output.path',       $input->getOption('out'));
        }
        if (null !== $input->getOption('no-colors')) {
            $container->setParameter('behat.formatter.colors', !$input->getOption('no-colors'));
        }
        if (null !== $input->getOption('no-time')) {
            $container->setParameter('behat.formatter.timer',  !$input->getOption('no-time'));
        }
        if (null !== $input->getOption('verbose')) {
            $container->setParameter('behat.formatter.verbose', $input->getOption('verbose'));
        }
        if (null !== $input->getOption('i18n')) {
            $container->setParameter('behat.formatter.locale',  $input->getOption('i18n'));
        }

        // Find proper features path
        $featuresPath = $container->getParameter('behat.features.path');
        if (is_dir($featuresPath . DIRECTORY_SEPARATOR . 'features')) {
            $featuresPath = $featuresPath . DIRECTORY_SEPARATOR . 'features';
            $container->setParameter('behat.features.path',         $featuresPath);
        } elseif (is_file($featuresPath)) {
            $container->setParameter('behat.features.path',         dirname($featuresPath));
            $container->setParameter('behat.features.load_path',    $featuresPath);
        }

        // Freeze container
        $container->compile();

        // Set Output Manager Output instance
        $container->get('behat.output_manager')->setOutput($output);

        // Translations
        $translator = $container->get('behat.translator');
        foreach ($this->findTranslationResources($container->getParameter('behat.i18n.path')) as $path) {
            $transId = basename($path, '.xliff');
            $translator->addResource('xliff', $path, $transId);
        }

        // Configure Gherkin manager
        $gherkin = $container->get('gherkin');
        if ($container->getParameter('gherkin.filter.name')) {
            $gherkin->addFilter(new NameFilter($container->getParameter('gherkin.filter.name')));
        }
        if ($container->getParameter('gherkin.filter.tags')) {
            $gherkin->addFilter(new TagFilter($container->getParameter('gherkin.filter.tags')));
        }

        // Add hooks files paths to container resources list
        $hooksContainer = $container->get('behat.hooks_container');
        foreach ((array) $container->getParameter('behat.hooks.file') as $path) {
            if (is_file($path)) {
                $hooksContainer->addResource('php', $path);
            }
        }

        // Add definitions files to container resources list
        $definitionsContainer = $container->get('behat.definitions_container');
        foreach ((array) $container->getParameter('behat.steps.path') as $stepsPath) {
            if (is_dir($stepsPath)) {
                foreach ($this->findDefinitionResources($stepsPath) as $path) {
                    $definitionsContainer->addResource('php', $path);
                }
            }
        }

        // Load features
        $features = $gherkin->load($container->getParameter('behat.features.load_path'));

        // Notify suite.run.before event & start timer
        $container->get('behat.event_dispatcher')->notify(new Event($container, 'suite.run.before'));
        $container->get('behat.statistics_collector')->startTimer();

        // Run features
        $result = 0;
        foreach ($features as $feature) {
            $tester = $container->get('behat.feature_tester');
            $result = max($result, $feature->accept($tester));
        }

        // Notify suite.run.after event
        $container->get('behat.statistics_collector')->finishTimer();
        $container->get('behat.event_dispatcher')->notify(new Event($container, 'suite.run.after'));

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
        $xmlLoader->load(__DIR__ . '/../../ServiceContainer/container.xml');

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
        $container->setParameter('behat.work.path', $cwd);
        $container->setParameter('behat.lib.path',  $behatPath = realpath(__DIR__ . '/../../../../../'));
        $container->setParameter('gherkin.lib.path',  realpath($behatPath . '/vendor/gherkin'));

        return $container;
    }

    /**
     * Find translations for Behat.
     *
     * @param   string  $i18nPath   xliff path
     * 
     * @return  mixed               files iterator
     */
    protected function findTranslationResources($i18nPath)
    {
        $finder = new Finder();
        $paths  = $finder->files()->name('*.xliff')->in($i18nPath);

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
