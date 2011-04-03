<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Yaml\Yaml,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\EventDispatcher\Event,
    Symfony\Component\Finder\Finder;

use Behat\Behat\DependencyInjection\BehatExtension,
    Behat\Behat\Formatter\FormatterInterface,
    Behat\Behat\Event\SuiteEvent;

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
     * Path tokens replacements.
     *
     * @var     array
     */
    protected $pathTokens = array(
        'BEHAT_CONFIG_PATH' => '',
        'BEHAT_WORK_PATH'   => '',
        'BEHAT_BASE_PATH'   => ''
    );

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
        $this->setDefinition(array(
            new InputArgument('features',
                InputArgument::OPTIONAL,
                'Feature(s) to run. Could be <info>directory path</info>, <info>single feature path</info> ' .
                'or scenario at specific feature line ("<info>*.feature:10</info>").'
            ),
            new InputOption('--config',         '-c',
                InputOption::VALUE_REQUIRED,
                '  ' .
                'Specify external configuration file to load. ' .
                '<info>behat.yml</info> or <info>config/behat.yml</info> will be used by default.'
            ),
            new InputOption('--profile',        null,
                InputOption::VALUE_REQUIRED,
                '      ' .
                'Specify configuration profile name to use. ' .
                'Define configuration profiles in <info>behat.yml</info>.'
            ),
            new InputOption('--out',            null,
                InputOption::VALUE_REQUIRED,
                '          ' .
                'Write formatter output to a file/directory instead of STDOUT.'
            ),
            new InputOption('--name',           '-n',
                InputOption::VALUE_REQUIRED,
                '    ' .
                'Only execute the feature elements (features or scenarios) which match part of the given name or regex.'
            ),
            new InputOption('--tags',           '-t',
                InputOption::VALUE_REQUIRED,
                '    ' .
                'Only execute the features or scenarios with tags matching tag filter expression.'
            ),
            new InputOption('--strict',         '-s',
                InputOption::VALUE_NONE,
                '  ' .
                'Fail if there are any undefined or pending steps.'
            ),


            new InputOption('--init',           null,
                InputOption::VALUE_NONE,
                '         ' .
                'Create features/ directory structure'
            ),
            new InputOption('--usage',          null,
                InputOption::VALUE_NONE,
                '        ' .
                'Print *.feature example in specified language (--lang).'
            ),
            new InputOption('--steps',          null,
                InputOption::VALUE_NONE,
                '        ' .
                'Print available steps in specified language (--lang).'
            ),


            new InputOption('--format',         '-f',
                InputOption::VALUE_REQUIRED,
                '  ' .
                'How to format features (Default: pretty). Available formats are ' .
                implode(', ',
                    array_map(function($name) {
                        return "<info>$name</info>";
                    }, array_keys($this->defaultFormatters))
                )
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
        ));
    }

    /**
     * {@inheritdoc}
     *
     * @uses    configureContainer()
     * @uses    printUsageExample()
     * @uses    locateFeaturesPaths()
     * @uses    loadBootstraps()
     * @uses    configureFormatter()
     * @uses    configureGherkinParser()
     * @uses    configureDefinitionDispatcher()
     * @uses    configureHookDispatcher()
     * @uses    configureEnvironmentBuilder()
     * @uses    configureEventDispathcer()
     * @uses    runFeatures()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->configureContainer($input->getOption('config'), $input->getOption('profile'));

        if ($input->getOption('usage')) {
            $this->printUsageExample($input, $container, $output);

            return 0;
        }

        if ($input->getOption('init')) {
            $this->createFeaturesPath($container, $output);

            return 0;
        }

        $featuresPaths = $this->locateFeaturesPaths($input, $container);
        $this->loadBootstraps($container);
        $formatter = $this->configureFormatter($input, $container, $output->isDecorated());
        $this->configureGherkinParser($input, $container);
        $this->configureDefinitionDispatcher($input, $container);

        if ($input->getOption('steps')) {
            $this->printAvailableSteps($input, $container, $output);

            return 0;
        }

        $this->configureHookDispatcher($input, $container);
        $this->configureEnvironmentBuilder($input, $container);
        $this->configureEventDispathcer($formatter, $container);

        $result = $this->runFeatures($featuresPaths, $container);

        if ($input->getOption('strict')) {
            return intval(0 < $result);
        } else {
            return intval(4 === $result);
        }
    }

    /**
     * Configures service container with or without provided configuration file.
     *
     * @param   string  $configFile DependencyInjection extension config file path (in YAML)
     * @param   string  $profile    profile name
     *
     * @return  Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function configureContainer($configFile = null, $profile = null)
    {
        $container  = new ContainerBuilder();
        $extension  = new BehatExtension();
        $config     = array();
        $cwd        = getcwd();

        $this->pathTokens['BEHAT_WORK_PATH'] = $cwd;
        $this->pathTokens['BEHAT_BASE_PATH'] = $cwd . DIRECTORY_SEPARATOR . 'features';

        if (null === $profile) {
            $profile = 'default';
        }

        if (null === $configFile) {
            if (is_file($cwd . DIRECTORY_SEPARATOR . 'behat.yml')) {
                $configFile = $cwd . DIRECTORY_SEPARATOR . 'behat.yml';
            } elseif (is_file($cwd . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'behat.yml')) {
                $configFile = $cwd . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'behat.yml';
            }
        }

        if (null !== $configFile) {
            $this->pathTokens['BEHAT_CONFIG_PATH'] = dirname($configFile);
            $config = $this->loadConfigurationFile($configFile, $profile);
        }

        $extension->load(array($config), $container);
        $container->compile();

        return $container;
    }

    /**
     * Loads information from YAML configuration file.
     *
     * @param   string  $configFile     path to the config file
     * @param   string  $profile        name of the config profile to use
     *
     * @return  array
     */
    protected function loadConfigurationFile($configFile, $profile = 'default')
    {
        $config = Yaml::load($configFile);

        $resultConfig = isset($config['default']) ? $config['default'] : array();
        if ('default' !== $profile && isset($config[$profile])) {
            $resultConfig = $this->configMergeRecursiveWithOverwrites($resultConfig, $config[$profile]);
        }

        if (isset($config['imports'])) {
            foreach ($config['imports'] as $importConfigFile) {
                $importConfigFile = $this->preparePath($importConfigFile);
                $resultConfig = $this->configMergeRecursiveWithOverwrites(
                    $resultConfig, $this->loadConfigurationFile($importConfigFile, $profile)
                );
            }
        }

        return $resultConfig;
    }

    /**
     * Prints features usage example in specified language (--lang) to the console.
     *
     * @param   Symfony\Component\Console\Input\InputInterface              $input      input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     * @param   Symfony\Component\Console\Input\OutputInterface             $output     output console
     */
    protected function printUsageExample(InputInterface $input, ContainerInterface $container,
                                      OutputInterface $output)
    {
        $keywords   = $container->get('gherkin.keywords');
        $dumper     = new KeywordsDumper($keywords);
        $lang       = $input->getOption('lang') ?: 'en';

        $output->setDecorated(false);
        $output->writeln($dumper->dump($lang) . "\n");
    }

    /**
     * Creates features path structure (initializes behat tests structure).
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     * @param   Symfony\Component\Console\Input\OutputInterface             $output     output console
     */
    protected function createFeaturesPath(ContainerInterface $container, OutputInterface $output)
    {
        $basePath       = $this->pathTokens['BEHAT_BASE_PATH'] . DIRECTORY_SEPARATOR;
        $featuresPath   = $this->preparePath($container->getParameter('behat.paths.features'));
        $supportPath    = $this->preparePath($container->getParameter('behat.paths.support'));
        $stepsPath      = $container->getParameter('behat.paths.steps');
        $stepsPath      = $this->preparePath($stepsPath[0]);
        $envPath        = $container->getParameter('behat.paths.environment');
        $envPath        = $this->preparePath($envPath[0]);
        $bootPath       = $container->getParameter('behat.paths.bootstrap');
        $bootPath       = $this->preparePath($bootPath[0]);

        if (!is_dir($featuresPath)) {
            mkdir($featuresPath, 0777, true);
            $output->writeln(
                '<info>+d</info> ' .
                str_replace($basePath, '', $featuresPath) .
                ' <comment>⎯ place your *.feature files here</comment>'
            );
        }

        if (!is_dir($stepsPath)) {
            mkdir($stepsPath, 0777, true);
            $output->writeln(
                '<info>+d</info> ' .
                str_replace($basePath, '', $stepsPath) .
                ' <comment>⎯ place step definition files here</comment>'
            );

            file_put_contents($stepsPath . DIRECTORY_SEPARATOR . 'steps.php', <<<DEFINITIONS
<?php

/**
 * Define your steps here with:
 *
 *     \$steps->Given('/REGEX/', function(\$world) {
 *         // do something or throw exception
 *     });
 */


DEFINITIONS
            );
            $output->writeln(
                '<info>+f</info> ' .
                str_replace($basePath, '', $stepsPath) . DIRECTORY_SEPARATOR . 'steps.php' .
                ' <comment>⎯ place some step definitions in this file</comment>'
            );
        }

        if (!is_dir($supportPath)) {
            mkdir($supportPath, 0777, true);
            $output->writeln(
                '<info>+d</info> ' .
                str_replace($basePath, '', $supportPath) .
                ' <comment>⎯ place support scripts and static files here</comment>'
            );

            file_put_contents($bootPath, <<<BOOTSTRAP
<?php

/**
 * Place bootstrap scripts here:
 *
 *     require_once 'PHPUnit/Autoload.php';
 *     require_once 'PHPUnit/Framework/Assert/Functions.php';
 */


BOOTSTRAP
            );
            $output->writeln(
                '<info>+f</info> ' .
                str_replace($basePath, '', $bootPath) .
                ' <comment>⎯ place bootstrap scripts in this file</comment>'
            );

            file_put_contents($envPath, <<<ENVIRONMENT
<?php

/**
 * Place environment initialization scripts here:
 *
 *     \$world->initialSum = 231;
 *     \$world->calc = function() {
 *         // ...
 *     };
 */


ENVIRONMENT
            );
            $output->writeln(
                '<info>+f</info> ' .
                str_replace($basePath, '', $envPath) .
                ' <comment>⎯ place environment initialization scripts in this file</comment>'
            );
        }
    }

    /**
     * Prints available step definitions.
     *
     * @param   Symfony\Component\Console\Input\InputInterface              $input      input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     * @param   Symfony\Component\Console\Input\OutputInterface             $output     output console
     */
    protected function printAvailableSteps(InputInterface $input, ContainerInterface $container,
                                        OutputInterface $output)
    {
        $dumper = $container->get('behat.definition_dumper');

        $output->setDecorated(false);
        $output->write($dumper->dump($input->getOption('lang') ?: 'en'));
    }

    /**
     * Locates features paths with provided input.
     *
     * @param   Symfony\Component\Console\Input\InputInterface              $input      input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     *
     * @return  IteratorAggregate|array
     *
     * @throws  RuntimeException    if provided in input path is not found
     */
    protected function locateFeaturesPaths(InputInterface $input, ContainerInterface $container)
    {
        $basePath       = '%BEHAT_WORK_PATH%' . DIRECTORY_SEPARATOR . 'features';
        $featuresPath   = $container->getParameter('behat.paths.features');
        $lineFilter     = '';

        if ($path = $input->getArgument('features')) {
            $matches = array();
            if (preg_match('/^(.*)\:(\d+)$/', $path, $matches)) {
                $path       = $matches[1];
                $lineFilter = ':' . $matches[2];
            }

            if (is_file($path)) {
                $basePath = dirname(realpath($path));
                $featuresPath = $path;
            } elseif (is_dir($path . DIRECTORY_SEPARATOR . 'features')) {
                $basePath = realpath($path) . DIRECTORY_SEPARATOR . 'features';
            } elseif (is_dir($path)) {
                $basePath = realpath($path);
            } else {
                throw new \RuntimeException("Path $path not found");
            }
        }

        $this->pathTokens['BEHAT_BASE_PATH'] = $this->preparePath($basePath);
        $featuresPath = $this->preparePath($featuresPath);

        if ('.feature' !== mb_substr($featuresPath, -8)) {
            $finder         = new Finder();
            $featuresPaths  = $finder->files()->name('*.feature')->in($featuresPath);
        } else {
            $featuresPaths  = (array) ($featuresPath . $lineFilter);
        }

        return $featuresPaths;
    }

    /**
     * Load bootstrap scripts (if they exists).
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     */
    protected function loadBootstraps(ContainerInterface $container)
    {
        foreach ((array) $container->getParameter('behat.paths.bootstrap') as $path) {
            $path = $this->preparePath($path);

            if (is_file($path)) {
                require_once($path);
            }
        }
    }

    /**
     * Configures Gherkin parser with provided input.
     *
     * @param   Symfony\Component\Console\Input\InputInterface              $input      input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     *
     * @return  Behat\Gherkin\Gherkin
     */
    protected function configureGherkinParser(InputInterface $input, ContainerInterface $container)
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
     * Configures formatter with provided input.
     *
     * @param   Symfony\Component\Console\Input\InputInterface              $input          input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container      service container
     * @param   boolean                                                     $isDecorated    is colorized
     *
     * @return  Behat\Behat\Formatter\FormatterInterface
     *
     * @throws  RuntimeException            if provided in input formatter name doesn't exists
     *
     * @uses    setupFormatter()
     */
    protected function configureFormatter(InputInterface $input, ContainerInterface $container, $isDecorated)
    {
        $formatterName = $input->getOption('format') ?: $container->getParameter('behat.formatter.name');

        if (class_exists($formatterName)) {
            $class = $formatterName;
        } elseif (isset($this->defaultFormatters[$formatterName])) {
            $class = $this->defaultFormatters[$formatterName];
        } else {
            throw new \RuntimeException("Unknown formatter: \"$formatterName\". " .
                'Available formatters are: ' . implode(', ', array_keys($this->defaultFormatters))
            );
        }

        $formatter = new $class();
        $this->setupFormatter($formatter, $input, $container, $isDecorated);

        return $formatter;
    }

    /**
     * Setup formatter with provided input.
     *
     * @param   Behat\Behat\Formatter\FormatterInterface                    $formatter      formatter instance
     * @param   Symfony\Component\Console\Input\InputInterface              $input          input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container      service container
     * @param   boolean                                                     $isDecorated    is colorized
     */
    protected function setupFormatter(FormatterInterface $formatter, InputInterface $input,
                                      ContainerInterface $container, $isDecorated)
    {
        $translator = $container->get('behat.translator');
        $formatter->setTranslator($translator);

        $formatter->setParameter('base_path',
            $this->pathTokens['BEHAT_BASE_PATH']
        );
        $formatter->setParameter('verbose',
            $input->getOption('verbose') ?: $container->getParameter('behat.formatter.verbose')
        );
        $formatter->setParameter('language',
            $input->getOption('lang') ?: $container->getParameter('behat.formatter.language')
        );

        if ($input->getOption('colors')) {
            $formatter->setParameter('decorated', true);
        } elseif ($input->getOption('no-colors')) {
            $formatter->setParameter('decorated', false);
        } elseif (null !== ($decorated = $container->getParameter('behat.formatter.decorated'))) {
            $formatter->setParameter('decorated', $decorated);
        } else {
            $formatter->setParameter('decorated', $isDecorated);
        }

        $formatter->setParameter('time',
            $input->getOption('no-time') ? false : $container->getParameter('behat.formatter.time')
        );
        $formatter->setParameter('multiline_arguments',
            $input->getOption('no-multiline') ? false : $container->getParameter('behat.formatter.multiline_arguments')
        );
        if ($out = $input->getOption('out')) {
            $formatter->setParameter('output_path', $this->preparePath($out));
        }

        foreach ($container->getParameter('behat.formatter.parameters') as $param => $value) {
            $formatter->setParameter($param, $value);
        }
    }

    /**
     * Configures environment builder with provided input.
     *
     * @param   Symfony\Component\Console\Input\InputInterface              $input      input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     *
     * @return  Behat\Behat\Environment\EnvironmentBuilder
     */
    protected function configureEnvironmentBuilder(InputInterface $input, ContainerInterface $container)
    {
        $builder = $container->get('behat.environment_builder');

        foreach ((array) $container->getParameter('behat.paths.environment') as $path) {
            $path = $this->preparePath($path);

            if (is_file($path)) {
                $builder->addResource($path);
            }
        }

        return $builder;
    }

    /**
     * Configures definition dispatcher with provided input.
     *
     * @param   Symfony\Component\Console\Input\InputInterface              $input      input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     *
     * @return  Behat\Behat\Definition\DefinitionDispatcher
     *
     * @uses    configureDefinitionsTranslations()
     */
    protected function configureDefinitionDispatcher(InputInterface $input, ContainerInterface $container)
    {
        $dispatcher = $container->get('behat.definition_dispatcher');

        foreach ((array) $container->getParameter('behat.paths.steps') as $path) {
            $path = $this->preparePath($path);

            if (is_dir($path)) {
                $finder = new Finder();
                $files  = $finder->files()->name('*.php')->in($path);

                foreach ($files as $file) {
                    $dispatcher->addResource('php', $file);
                }
            }
        }

        $this->configureDefinitionsTranslations($input, $container);

        return $dispatcher;
    }

    /**
     * Configures definitions translations.
     *
     * @param   Symfony\Component\Console\Input\InputInterface              $input      input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     *
     * @return  Symfony\Component\Translation\Translator
     */
    protected function configureDefinitionsTranslations(InputInterface $input, ContainerInterface $container)
    {
        $translator = $container->get('behat.translator');

        foreach ((array) $container->getParameter('behat.paths.steps.i18n') as $path) {
            $path = $this->preparePath($path);

            if (is_dir($path)) {
                $finder = new Finder();
                $files  = $finder->files()->name('*.xliff')->in($path);

                foreach ($files as $file) {
                    $translator->addResource('xliff', $file, basename($file, '.xliff'), 'behat.definitions');
                }
            }
        }

        return $translator;
    }

    /**
     * Configures hook dispatcher with provided input.
     *
     * @param   Symfony\Component\Console\Input\InputInterface              $input      input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     *
     * @return  Behat\Behat\Hook\HookDispatcher
     */
    protected function configureHookDispatcher(InputInterface $input, ContainerInterface $container)
    {
        $dispatcher = $container->get('behat.hook_dispatcher');

        foreach ((array) $container->getParameter('behat.paths.hooks') as $path) {
            $path = $this->preparePath($path);

            if (is_file($path)) {
                $dispatcher->addResource('php', $path);
            }
        }

        return $dispatcher;
    }

    /**
     * Configures event dispatcher.
     *
     * @param   Behat\Behat\Formatter\FormatterInterface                    $formatter  output formatter
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     *
     * @return  Behat\Behat\EventDispatcher\EventDispatcher
     */
    protected function configureEventDispathcer(FormatterInterface $formatter, ContainerInterface $container)
    {
        $dispatcher = $container->get('behat.event_dispatcher');

        $dispatcher->addSubscriber($container->get('behat.hook_dispatcher'), 10);
        $dispatcher->addSubscriber($container->get('behat.logger'), 0);
        $dispatcher->addSubscriber($formatter, -10);

        return $dispatcher;
    }

    /**
     * Runs specified features.
     *
     * @param   Symfony\Component\Console\Input\InputInterface              $input      input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     *
     * @return  integer
     */
    protected function runFeatures($featuresPaths, ContainerInterface $container)
    {
        $result     = 0;
        $gherkin    = $container->get('gherkin');
        $dispatcher = $container->get('behat.event_dispatcher');
        $logger     = $container->get('behat.logger');

        $dispatcher->dispatch('beforeSuite', new SuiteEvent($logger));

        foreach ($featuresPaths as $path) {
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
     * Prepare path to find/load methods.
     *
     * Fix directory separators, replace path tokens with configured ones,
     * prepend single filenames with CWD path.
     *
     * @param   string  $path
     *
     * @return  string
     *
     * @uses    pathTokens
     */
    protected function preparePath($path)
    {
        $pathTokens = $this->pathTokens;
        $path = preg_replace_callback('/%([^%]+)%/', function($matches) use($pathTokens) {
            $name = $matches[1];
            if (defined($name)) {
                return constant($name);
            } elseif (isset($pathTokens[$name])) {
                return $pathTokens[$name];
            }
            return $matches[0];
        }, $path);

        $path = str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', '/', $path));

        if (!file_exists($path)) {
            foreach (explode(':', get_include_path()) as $libPath) {
                if (file_exists($libPath . DIRECTORY_SEPARATOR . $path)) {
                    $path = $libPath . DIRECTORY_SEPARATOR . $path;
                    break;
                }
            }
        }

        if (!file_exists($path) && file_exists(getcwd() . DIRECTORY_SEPARATOR . $path)) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }

        return $path;
    }

    /**
     * Merges two arrays into one with overwrite. It works the same as array_merge_recursive, but
     * overwrites non-array values instead of turning them into arrays.
     *
     * @param   array  $array1  to
     * @param   array  $array2  from
     *
     * @return  array
     */
    private function configMergeRecursiveWithOverwrites($array1, $array2)
    {
        foreach($array2 as $key => $val) {
            if (array_key_exists($key, $array1) && is_array($val)) {
                $array1[$key] = $this->configMergeRecursiveWithOverwrites($array1[$key], $val);
            } elseif (is_numeric($key)) {
                $array1[] = $val;
            } else {
                $array1[$key] = $val;
            }
        }

        return $array1;
    }
}
