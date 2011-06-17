<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\DependencyInjection\BehatExtension,
    Behat\Behat\Definition\DefinitionPrinter,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\PathLocator;

use Behat\Gherkin\Filter\NameFilter,
    Behat\Gherkin\Filter\TagFilter,
    Behat\Gherkin\Keywords\KeywordsDumper;

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
     * Default Behat formatters.
     *
     * @var     array
     */
    protected $defaultFormatters = array(
        'pretty'    => 'Behat\Behat\Formatter\PrettyFormatter',
        'progress'  => 'Behat\Behat\Formatter\ProgressFormatter',
        'html'      => 'Behat\Behat\Formatter\HtmlFormatter',
        'junit'     => 'Behat\Behat\Formatter\JUnitFormatter'
    );

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('behat');
        $this->setDefinition(array_merge(
            array(
                new InputArgument('features',
                    InputArgument::OPTIONAL,
                    'Feature(s) to run. Could be dir (<comment>features/</comment>), ' .
                    'feature (<comment>*.feature</comment>) or scenario at specific line ' .
                    '(<comment>*.feature:10</comment>).'
                ),
            ),
            $this->getInitOptions(),
            $this->getDemonstrationOptions(),
            $this->getConfigurationOptions(),
            $this->getFilterOptions(),
            $this->getFormatterOptions(),
            $this->getRunOptions()
        ));
    }

    /**
     * Returns array of run options for command.
     *
     * @return  array
     */
    protected function getRunOptions()
    {
        return array(
            new InputOption('--strict',         null,
                InputOption::VALUE_NONE,
                '       ' .
                'Fail if there are any undefined or pending steps.'
            ),
            new InputOption('--rerun',          null,
                InputOption::VALUE_REQUIRED,
                '        ' .
                'Save list of failed scenarios into file or use existing file to run only scenarios from it.'
            ),
        );
    }

    /**
     * Returns array of configuration options for command.
     *
     * @return  array
     */
    protected function getConfigurationOptions()
    {
        return array(
            new InputOption('--config',         '-c',
                InputOption::VALUE_REQUIRED,
                '  ' .
                'Specify external configuration file to load. ' .
                '<comment>behat.yml</comment> or <comment>config/behat.yml</comment> will be used by default.'
            ),
            new InputOption('--profile',        '-p',
                InputOption::VALUE_REQUIRED,
                ' ' .
                'Specify configuration profile to use. ' .
                'Define profiles in config file (<info>--config</info>).'
            ),
        );
    }

    /**
     * Returns array of filter options for command.
     *
     * @return  array
     */
    protected function getFilterOptions()
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
                'Only execute the features or scenarios with tags matching tag filter expression.'
            ),
        );
    }

    /**
     * Returns array of init options.
     *
     * @return  array
     */
    protected function getInitOptions()
    {
        return array(
            new InputOption('--init',           null,
                InputOption::VALUE_NONE,
                '         ' .
                'Create <comment>features</comment> directory structure.'
            ),
        );
    }

    /**
     * Returns array of demonstration options for command
     *
     * @return  array
     */
    protected function getDemonstrationOptions()
    {
        return array(
            new InputOption('--usage',          null,
                InputOption::VALUE_NONE,
                '        ' .
                'Print *.feature example in specified language (<info>--lang</info>).'
            ),
            new InputOption('--steps',          null,
                InputOption::VALUE_NONE,
                '        ' .
                'Print available steps in specified language (<info>--lang</info>).'
            ),
        );
    }

    /**
     * Returns array of formatter options for command.
     *
     * @return  array
     */
    protected function getFormatterOptions()
    {
        return array(
            new InputOption('--format',         '-f',
                InputOption::VALUE_REQUIRED,
                '  ' .
                'How to format features. <comment>pretty</comment> is default. Available formats are ' .
                implode(', ',
                    array_map(function($name) {
                        return "<comment>$name</comment>";
                    }, array_keys($this->defaultFormatters))
                )
            ),
            new InputOption('--out',            null,
                InputOption::VALUE_REQUIRED,
                '          ' .
                'Write formatter output to a file/directory instead of STDOUT (<comment>output_path</comment>).'
            ),
            new InputOption('--colors',         null,
                InputOption::VALUE_NONE,
                '       ' .
                'Force Behat to use ANSI color in the output.'
            ),
            new InputOption('--no-colors',      null,
                InputOption::VALUE_NONE,
                '    ' .
                'Do not use ANSI color in the output.'
            ),
            new InputOption('--no-time',        null,
                InputOption::VALUE_NONE,
                '      ' .
                'Hide time in output.'
            ),
            new InputOption('--lang',           null,
                InputOption::VALUE_REQUIRED,
                '         ' .
                'Print formatter output in particular language.'
            ),
            new InputOption('--no-multiline',   null,
                InputOption::VALUE_NONE,
                ' ' .
                'No multiline arguments in output.'
            ),
        );
    }

    /**
     * {@inheritdoc}
     *
     * @uses    createContainer()
     * @uses    locateBasePath()
     * @uses    createFormatter()
     * @uses    initFeaturesDirectoryStructure()
     * @uses    demonstrateUsageExample()
     * @uses    runFeatures()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->createContainer(
            $input->hasOption('config')     ? $input->getOption('config')   : null,
            $input->hasOption('profile')    ? $input->getOption('profile')  : null
        );
        $locator = $container->get('behat.path_locator');

        // locate base path
        $this->locateBasePath($locator, $input);

        // load bootstrap files
        foreach ($locator->locateBootstrapFilesPaths() as $path) {
            require_once($path);
        }

        // init features directory structure
        if ($input->hasOption('init') && $input->getOption('init')) {
            $this->initFeaturesDirectoryStructure($locator, $output);
            return 0;
        }

        // we don't want to init, so we check, that features path exists
        if (!is_dir($featuresPath = $locator->getFeaturesPath())) {
            throw new \InvalidArgumentException("Path \"$featuresPath\" not found");
        }

        // init translator
        $translator = $container->get('behat.translator');

        // create formatter
        $formatter = $this->createFormatter(
            $input->getOption('format') ?: $container->getParameter('behat.formatter.name')
        );

        // configure formatter
        $formatter->setTranslator($translator);
        $formatter->setParameter('base_path', $locator->getWorkPath());
        $formatter->setParameter('support_path', $locator->getBootstrapPath());
        $formatter->setParameter('verbose',
            $input->getOption('verbose') ?: $container->getParameter('behat.formatter.verbose')
        );
        $formatter->setParameter('language',
            $input->getOption('lang') ?: $container->getParameter('behat.formatter.language')
        );
        if ($input->getOption('colors')) {
            $output->setDecorated(true);
            $formatter->setParameter('decorated', true);
        } elseif ($input->getOption('no-colors')) {
            $output->setDecorated(false);
            $formatter->setParameter('decorated', false);
        } elseif (null !== ($decorated = $container->getParameter('behat.formatter.decorated'))) {
            $output->setDecorated($decorated);
            $formatter->setParameter('decorated', $decorated);
        } else {
            $formatter->setParameter('decorated', $output->isDecorated());
        }
        $formatter->setParameter('time',
            $input->getOption('no-time') ? false : $container->getParameter('behat.formatter.time')
        );
        $formatter->setParameter('multiline_arguments',
            $input->getOption('no-multiline')
                ? false
                : $container->getParameter('behat.formatter.multiline_arguments')
        );
        if ($out = $input->getOption('out') ?: $locator->getOutputPath()) {
            // get realpath
            if (!file_exists($out)) {
                touch($out);
                $out = realpath($out);
                unlink($out);
            } else {
                $out = realpath($out);
            }
            $formatter->setParameter('output_path', $out);
            $formatter->setParameter('decorated', (Boolean) $input->getOption('colors'));
        }
        foreach ($container->getParameter('behat.formatter.parameters') as $param => $value) {
            $formatter->setParameter($param, $value);
        }

        // configure gherkin filters
        $gherkinParser = $container->get('gherkin');
        if ($name = ($input->getOption('name') ?: $container->getParameter('gherkin.filters.name'))) {
            $gherkinParser->addFilter(new NameFilter($name));
        }
        if ($tags = ($input->getOption('tags') ?: $container->getParameter('gherkin.filters.tags'))) {
            $gherkinParser->addFilter(new TagFilter($tags));
        }

        // read main dispatchers
        $contextReader          = $container->get('behat.context_reader');
        $definitionDispatcher   = $container->get('behat.definition_dispatcher');
        $hookDispatcher         = $container->get('behat.hook_dispatcher');

        // read annotations
        $contextReader->read();

        // logger
        $logger = $container->get('behat.logger');

        // helpers
        if ($input->hasOption('usage') && $input->getOption('usage')) {
            $this->demonstrateUsageExample(
                $container->get('gherkin.keywords_dumper'), $input->getOption('lang') ?: 'en', $output
            );
            return 0;
        } elseif ($input->hasOption('steps') && $input->getOption('steps')) {
            $container->get('behat.definition_printer')->printDefinitions(
                $output, $input->getOption('lang') ?: 'en'
            );
            return 0;
        }

        // subscribe event listeners
        $eventDispatcher = $container->get('behat.event_dispatcher');
        $eventDispatcher->addSubscriber($hookDispatcher, 10);
        $eventDispatcher->addSubscriber($logger, 0);
        $eventDispatcher->addSubscriber($formatter, -10);

        // rerun data collector
        $rerunDataCollector = $container->get('behat.rerun_data_collector');
        if ($input->getOption('rerun') || $container->getParameter('behat.options.rerun')) {
            $rerunDataCollector->setRerunFile(
                $input->getOption('rerun') ?: $container->getParameter('behat.options.rerun')
            );
            $eventDispatcher->addSubscriber($rerunDataCollector, 0);
        }

        // run features
        $result = $this->runFeatures(
            $rerunDataCollector->hasFailedScenarios()
                ? $rerunDataCollector->getFailedScenariosPaths()
                : $locator->locateFeaturesPaths(),
            $container
        );
        if ($input->getOption('strict') || $container->getParameter('behat.options.strict')) {
            return intval(0 < $result);
        } else {
            return intval(4 === $result);
        }
    }

    /**
     * Creates service container with or without provided configuration file.
     *
     * @param   string  $configFile DependencyInjection extension config file path (in YAML)
     * @param   string  $profile    profile name
     *
     * @return  Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function createContainer($configFile = null, $profile = null)
    {
        $container  = new ContainerBuilder();
        $extension  = new BehatExtension();
        $cwd        = getcwd();

        if (null === $profile) {
            $profile = 'default';
        }

        if (null === $configFile) {
            if (is_file($cwd.DIRECTORY_SEPARATOR.'behat.yml')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'behat.yml';
            } elseif (is_file($cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml';
            }
        }

        if (null !== $configFile) {
            $config = $extension->loadFromFile($configFile, $profile, $container);
        } else {
            $config = $extension->load(array(array()), $container);
        }
        $container->compile();

        if (null !== $configFile) {
            $container->get('behat.path_locator')->setPathConstant('BEHAT_CONFIG_PATH', dirname($configFile));
        }

        return $container;
    }

    /**
     * Creates formatter with provided input.
     *
     * @param   string  $formatterName  formatter name or class
     *
     * @return  Behat\Behat\Formatter\FormatterInterface
     *
     * @throws  RuntimeException            if provided in input formatter name doesn't exists
     *
     * @uses    setupFormatter()
     */
    protected function createFormatter($formatterName)
    {
        if (class_exists($formatterName)) {
            $class = $formatterName;
        } elseif (isset($this->defaultFormatters[$formatterName])) {
            $class = $this->defaultFormatters[$formatterName];
        } else {
            throw new \RuntimeException("Unknown formatter: \"$formatterName\". " .
                'Available formatters are: ' . implode(', ', array_keys($this->defaultFormatters))
            );
        }

        $refClass = new \ReflectionClass($class);
        if (!$refClass->implementsInterface('Behat\Behat\Formatter\FormatterInterface')) {
            throw new \RuntimeException(sprintf(
                'Cannot use "%s" as formatter as it doesn\'t implement Behat\Behat\Formatter\FormatterInterface',
                $class
            ));
        }

        return new $class();
    }

    /**
     * Locates behat base path.
     *
     * @param   Behat\Behat\PathLocator                         $locator    path locator
     * @param   Symfony\Component\Console\Input\InputInterface  $input      input
     *
     * @return  string
     */
    protected function locateBasePath(PathLocator $locator, InputInterface $input)
    {
        return $locator->locateBasePath($input->getArgument('features'));
    }

    /**
     * Runs specified features.
     *
     * @param   array                                                       $paths      features paths
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     *
     * @return  integer
     */
    protected function runFeatures(array $paths, ContainerInterface $container)
    {
        $result     = 0;
        $gherkin    = $container->get('gherkin');
        $dispatcher = $container->get('behat.event_dispatcher');
        $logger     = $container->get('behat.logger');

        $dispatcher->dispatch('beforeSuite', new SuiteEvent($logger));

        foreach ($paths as $path) {
            $features = $gherkin->load((string) $path);

            foreach ($features as $feature) {
                $tester = $container->get('behat.tester.feature');
                $result = max($result, $feature->accept($tester));
            }
        }

        $dispatcher->dispatch('afterSuite', new SuiteEvent($logger));

        return $result;
    }

    /**
     * Creates features path structure (initializes behat tests structure).
     *
     * @param   Behat\Behat\PathLocator                             $locator    path locator
     * @param   Symfony\Component\Console\Input\OutputInterface     $output     output console
     */
    protected function initFeaturesDirectoryStructure(PathLocator $locator, OutputInterface $output)
    {
        $basePath       = $locator->getWorkPath() . DIRECTORY_SEPARATOR;
        $featuresPath   = $locator->getFeaturesPath();
        $bootstrapPath  = $locator->getBootstrapPath();

        if (!is_dir($featuresPath)) {
            mkdir($featuresPath, 0777, true);
            $output->writeln(
                '<info>+d</info> ' .
                str_replace($basePath, '', $featuresPath) .
                ' <comment>- place your *.feature files here</comment>'
            );
        }

        if (!is_dir($bootstrapPath)) {
            mkdir($bootstrapPath, 0777, true);
            $output->writeln(
                '<info>+d</info> ' .
                str_replace($basePath, '', $bootstrapPath) .
                ' <comment>- place bootstrap scripts and static files here</comment>'
            );

            copy(__DIR__ . '/../../Skeleton/FeaturesContext.php', $bootstrapPath . DIRECTORY_SEPARATOR . 'FeaturesContext.php');
            $output->writeln(
                '<info>+f</info> ' .
                str_replace($basePath, '', $bootstrapPath) . DIRECTORY_SEPARATOR . 'FeaturesContext.php' .
                ' <comment>- place your features related code here</comment>'
            );
        }
    }

    /**
     * Prints features usage example in specified language (--lang) to the console.
     *
     * @param   Behat\Gherkin\Keywords\KeywordsDumper           $dumper     keywords dumper
     * @param   string                                          $lang       locale name
     * @param   Symfony\Component\Console\Input\OutputInterface $output     output console
     */
    protected function demonstrateUsageExample(KeywordsDumper $dumper, $lang, OutputInterface $output)
    {
        $output->setDecorated(false);
        $output->writeln($dumper->dump($lang) . "\n", OutputInterface::OUTPUT_RAW);
    }
}
