<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Yaml\Yaml,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\EventDispatcher\Event,
    Symfony\Component\Finder\Finder;

use Behat\Behat\DependencyInjection\BehatExtension;

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
 * Behat console command.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatCommand extends Command
{
    /**
     * Path tokens to replace.
     *
     * @var     array
     */
    protected $pathTokens = array(
        'BEHAT_CONFIG_PATH' => '',
        'BEHAT_WORK_PATH'   => '',
        'BEHAT_BASE_PATH'   => ''
    );

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('behat');
        $this->setDefinition(array(
            new InputArgument('features',
                InputArgument::OPTIONAL,
                'Features path'
            ),
            new InputOption('--config',         '-c',
                InputOption::VALUE_REQUIRED,
                'Specify external configuration file to load (*.xml or *.yml).'
            ),
            new InputOption('--format',         '-f',
                InputOption::VALUE_REQUIRED,
                'How to format features (Default: pretty). Available formats is pretty, progress, html.'
            ),
            new InputOption('--out',            null,
                InputOption::VALUE_REQUIRED,
                'Write output to a file/directory instead of STDOUT.'
            ),
            new InputOption('--name',           '-n',
                InputOption::VALUE_REQUIRED,
                'Only execute the feature elements (features or scenarios) which match part of the given name.'
            ),
            new InputOption('--tags',           '-t',
                InputOption::VALUE_REQUIRED,
                'Only execute the features or scenarios with tags matching expression.'
            ),
            new InputOption('--lang',           null,
                InputOption::VALUE_REQUIRED,
                'Print formatters output in particular language.'
            ),
            new InputOption('--verbose',        '-v',
                InputOption::VALUE_NONE,
                'Increase verbosity of fail messages.'
            ),
            new InputOption('--no-colors',      '-C',
                InputOption::VALUE_NONE,
                'Do not use ANSI color in the output.'
            ),
            new InputOption('--no-time',        '-T',
                InputOption::VALUE_NONE,
                'Hide time in output.'
            ),
            new InputOption('--help',           '-h',
                InputOption::VALUE_NONE,
                'Display this help message.'
            ),
            new InputOption('--version',        '-V',
                InputOption::VALUE_NONE,
                'Display this program version.'
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container      = $this->configureContainer($input->getOption('config'));
        $featuresPaths  = $this->locateFeaturesPaths($input, $container);
        $formatter      = $this->configureFormatter($input, $container);

        $this->configureGherkinParser($input, $container);
        $this->configureDefinitionDispatcher($input, $container);
        $this->configureHookDispatcher($input, $container);
        $this->configureEnvironmentBuilder($input, $container);

        $eventDispatcher = $container->get('behat.event_dispatcher');
        $eventDispatcher->bindContainerEventListeners($container);
        $eventDispatcher->bindFormatterEventListeners($formatter);

        $result = $this->runFeatures($featuresPaths, $container);

        return intval(0 < $result);
    }

    /**
     * Configure service container.
     *
     * @param   string  $configFile configuration file (YAML)
     *
     * @return  \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function configureContainer($configFile = null)
    {
        $container  = new ContainerBuilder();
        $extension  = new BehatExtension();
        $config     = array();

        $this->pathTokens['BEHAT_WORK_PATH'] = getcwd();

        if (null === $configFile) {
            if (is_file($this->pathTokens['BEHAT_WORK_PATH'] . '/behat.yml')) {
                $configFile = $this->pathTokens['BEHAT_WORK_PATH'] . '/behat.yml';
            } elseif (is_file($this->pathTokens['BEHAT_WORK_PATH'] . '/config/behat.yml')) {
                $configFile = $this->pathTokens['BEHAT_WORK_PATH'] . '/config/behat.yml';
            }
        }

        if (null !== $configFile) {
            $this->pathTokens['BEHAT_CONFIG_PATH'] = dirname($configFile);

            $config = Yaml::load($configFile);
        }

        $extension->configLoad($config, $container);
        $container->compile();

        return $container;
    }

    /**
     * Locate features paths with provided input.
     *
     * @param   InputInterface      $input      input instance
     * @param   ContainerBuilder    $container  service container
     *
     * @return  \Iterator
     */
    protected function locateFeaturesPaths(InputInterface $input, ContainerBuilder $container)
    {
        $basePath       = $container->getParameter('behat.paths.base');
        $featuresPath   = $container->getParameter('behat.paths.features');

        if ($path = $input->getArgument('features')) {
            if (is_file(($path = realpath($path)))) {
                $basePath       = dirname($path);
                $featuresPath   = $path;
            } elseif (is_dir($path)) {
                $basePath       = $path;
            } elseif (is_dir($path . '/features')) {
                $basePath       = $path . '/features';
            } else {
                throw new \InvalidArgumentException("Path $path not found");
            }
        }

        $this->pathTokens['BEHAT_BASE_PATH'] = $this->replacePathTokens($basePath);
        $featuresPath = $this->replacePathTokens($featuresPath);

        if ('.feature' !== mb_substr($featuresPath, -8)) {
            $finder         = new Finder();
            $featuresPaths  = $finder->files()->name('*.feature')->in($featuresPath);
        } else {
            $featuresPaths  = (array) $featuresPath;
        }

        return $featuresPaths;
    }

    /**
     * Configure Gherkin parser with provided input.
     *
     * @param   InputInterface      $input      input instance
     * @param   ContainerBuilder    $container  service container
     *
     * @return  \Behat\Gherkin\Gherkin
     */
    protected function configureGherkinParser(InputInterface $input, ContainerBuilder $container)
    {
        $gherkinParser = $container->get('gherkin');

        if ($name = $input->getOption('name')) {
            $gherkinParser->addFilter(new NameFilter($name));
        } elseif ($name = $container->getParameter('gherkin.filter.name')) {
            $gherkinParser->addFilter(new NameFilter($name));
        }
        if ($tags = $input->getOption('tags')) {
            $gherkinParser->addFilter(new TagFilter($tags));
        } elseif ($filter = $container->getParameter('gherkin.filter.tags')) {
            $gherkinParser->addFilter(new TagFilter($tags));
        }

        return $gherkinParser;
    }

    /**
     * Configure formatter with provided input.
     *
     * @param   InputInterface      $input      input instance
     * @param   ContainerBuilder    $container  service container
     *
     * @return  \Behat\Behat\Formatter\FormatterInterface
     */
    protected function configureFormatter(InputInterface $input, ContainerBuilder $container)
    {
        $formatterName = $input->getOption('format') ?: $container->getParameter('behat.formatter.name');

        if (false !== mb_strpos($formatterName, '\\')) {
            if (!class_exists($formatterName)) {
                throw new \InvalidArgumentException("Class $formatterName doesn't exists");
            }

            $class = $formatterName;
        } else {
            switch ($formatterName) {
                case 'progress':
                    $class = 'Behat\Behat\Formatter\ProgressFormatter';
                    break;
                case 'pretty':
                default:
                    $class = 'Behat\Behat\Formatter\PrettyFormatter';
            }
        }

        $translator = $container->get('behat.translator');
        $formatter  = new $class($translator);

        $formatter->setParameter('base_path',
            $this->pathTokens['BEHAT_BASE_PATH']
        );
        $formatter->setParameter('verbose',
            $input->getOption('verbose') ?: $container->getParameter('behat.formatter.verbose')
        );
        $formatter->setParameter('language',
            $input->getOption('lang') ?: $container->getParameter('behat.formatter.language')
        );
        $formatter->setParameter('decorated',
            $input->getOption('no-colors') ? false : $container->getParameter('behat.formatter.decorated')
        );
        $formatter->setParameter('time',
            $input->getOption('no-time') ? false : $container->getParameter('behat.formatter.time')
        );

        return $formatter;
    }

    /**
     * Configure environment builder with provided input.
     *
     * @param   InputInterface      $input      input instance
     * @param   ContainerBuilder    $container  service container
     *
     * @return  \Behat\Behat\Environment\EnvironmentBuilder
     */
    protected function configureEnvironmentBuilder(InputInterface $input, ContainerBuilder $container)
    {
        $builder = $container->get('behat.environment_builder');

        foreach ((array) $container->getParameter('behat.paths.environment') as $path) {
            $path = $this->replacePathTokens($path);

            if (is_file($path)) {
                $builder->addResource($path);
            }
        }

        return $builder;
    }

    /**
     * Configure definition dispatcher with provided input.
     *
     * @param   InputInterface      $input      input instance
     * @param   ContainerBuilder    $container  service container
     *
     * @return  \Behat\Behat\Definition\DefinitionDispatcher
     */
    protected function configureDefinitionDispatcher(InputInterface $input, ContainerBuilder $container)
    {
        $dispatcher = $container->get('behat.definition_dispatcher');

        foreach ((array) $container->getParameter('behat.paths.steps') as $path) {
            $path = $this->replacePathTokens($path);

            if (is_dir($path)) {
                $finder = new Finder();
                $files  = $finder->files()->name('*.php')->in($path);

                foreach ($files as $file) {
                    $dispatcher->addResource('php', $file);
                }
            }
        }

        return $dispatcher;
    }

    /**
     * Configure hook dispatcher with provided input.
     *
     * @param   InputInterface      $input      input instance
     * @param   ContainerBuilder    $container  service container
     *
     * @return  \Behat\Behat\Hook\HookDispatcher
     */
    protected function configureHookDispatcher(InputInterface $input, ContainerBuilder $container)
    {
        $dispatcher = $container->get('behat.hook_dispatcher');

        foreach ((array) $container->getParameter('behat.paths.hooks') as $path) {
            $path = $this->replacePathTokens($path);

            if (is_file($path)) {
                $dispatcher->addResource('php', $path);
            }
        }

        return $dispatcher;
    }

    /**
     * Run specified features.
     *
     * @param   \IteratorAggregate  $featuresPaths  features paths iterator
     * @param   ContainerBuilder    $container      service container
     *
     * @return  integer
     */
    protected function runFeatures($featuresPaths, ContainerBuilder $container)
    {
        $result     = 0;
        $gherkin    = $container->get('gherkin');
        $dispatcher = $container->get('behat.event_dispatcher');
        $logger     = $container->get('behat.logger');

        $dispatcher->notify(new Event($logger, 'suite.before'));

        foreach ($featuresPaths as $path) {
            $features = $gherkin->load((string) $path);

            foreach ($features as $feature) {
                $tester = $container->get('behat.tester.feature');
                $result = max($result, $feature->accept($tester));
            }
        }

        $dispatcher->notify(new Event($logger, 'suite.after'));

        return $result;
    }

    /**
     * Replace specific path tokens with values.
     *
     * @param   string  $path   input path
     *
     * @return  string
     */
    protected function replacePathTokens($path)
    {
        foreach ($this->pathTokens as $name => $value) {
            $path = str_replace($name, $value, $path);
        }

        return $path;
    }
}
