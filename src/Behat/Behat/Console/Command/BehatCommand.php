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

use Behat\Gherkin\Keywords\KeywordsDumper;

use Behat\Behat\Console\Processor\FormatProcessor,
    Behat\Behat\Console\Processor\GherkinProcessor;

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
    private $formatProcessor;
    private $gherkinProcessor;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->formatProcessor = new FormatProcessor();
        $this->gherkinProcessor = new GherkinProcessor();

        $this->setName('behat');
        $this->setDefinition(array_merge(
            array(
                new InputArgument('features',
                    InputArgument::OPTIONAL,
                    'Feature(s) to run. Could be a dir (<comment>features/</comment>), ' .
                    'a feature (<comment>*.feature</comment>) or a scenario at specific line ' .
                    '(<comment>*.feature:10</comment>).'
                ),
            ),
            $this->getInitOptions(),
            $this->getDemonstrationOptions(),
            $this->getConfigurationOptions(),
            $this->gherkinProcessor->getInputOptions(),
            $this->formatProcessor->getInputOptions(),
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
                'Define profiles in config file (<info>--config</info>).'."\n"
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
            new InputOption('--story-syntax',    null,
                InputOption::VALUE_NONE,
                ' ' .
                'Print *.feature example in specified language (<info>--lang</info>).'
            ),
            new InputOption('--definitions',    null,
                InputOption::VALUE_NONE,
                '  ' .
                'Print available step definitions in specified language (<info>--lang</info>).'."\n"
            ),
        );
    }

    /**
     * {@inheritdoc}
     *
     * @uses    createContainer()
     * @uses    locateBasePath()
     * @uses    getContextClass()
     * @uses    createFormatter()
     * @uses    initFeaturesDirectoryStructure()
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

        // init features directory structure
        if ($input->hasOption('init') && $input->getOption('init')) {
            $this->initFeaturesDirectoryStructure($locator, $output);
            return 0;
        }

        // load bootstrap files
        foreach ($locator->locateBootstrapFilesPaths() as $path) {
            require_once($path);
        }

        // guess and set context class
        $container->get('behat.context_dispatcher')->setContextClass(
            $this->getContextClass($input, $container)
        );

        // we don't want to init, so we check, that features path exists
        if (!is_dir($featuresPath = $locator->getFeaturesPath())) {
            throw new \InvalidArgumentException("Features path \"$featuresPath\" does not exist");
        }

        $this->formatProcessor->process($container, $input, $output);
        $this->gherkinProcessor->process($container, $input, $output);

        // read main dispatchers
        $contextReader          = $container->get('behat.context_reader');
        $definitionDispatcher   = $container->get('behat.definition_dispatcher');
        $hookDispatcher         = $container->get('behat.hook_dispatcher');

        // read annotations
        $contextReader->read();

        // logger
        $logger = $container->get('behat.logger');

        // helpers
        if ($input->hasOption('story-syntax') && $input->getOption('story-syntax')) {
            $container->get('behat.help_printer.story_syntax')->printSyntax(
                $output, $input->getOption('lang') ?: 'en'
            );

            return 0;
        } elseif ($input->hasOption('definitions') && $input->getOption('definitions')) {
            $container->get('behat.help_printer.definitions')->printDefinitions(
                $output, $input->getOption('lang') ?: 'en'
            );

            return 0;
        }

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
     * Guesses and returns feature context class.
     *
     * @param   Symfony\Component\Console\Input\InputInterface              $input      input instance
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     *
     * @return  string
     */
    protected function getContextClass(InputInterface $input, ContainerInterface $container)
    {
        return $container->getParameter('behat.context.class');
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

        $dispatcher->dispatch('beforeSuite', new SuiteEvent($logger, false));

        // catch app interruption
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGINT, function() use($dispatcher, $logger) {
                $dispatcher->dispatch('afterSuite', new SuiteEvent($logger, false));
                exit(0);
            });
        }

        // read features from paths
        foreach ($paths as $path) {
            $features = $gherkin->load((string) $path);

            // and run them in FeatureTester
            foreach ($features as $feature) {
                $tester = $container->get('behat.tester.feature');
                $result = max($result, $feature->accept($tester));
            }
        }

        $dispatcher->dispatch('afterSuite', new SuiteEvent($logger, true));

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
        $basePath       = realpath($locator->getWorkPath()) . DIRECTORY_SEPARATOR;
        $featuresPath   = $locator->getFeaturesPath();
        $bootstrapPath  = $locator->getBootstrapPath();

        if (!is_dir($featuresPath)) {
            mkdir($featuresPath, 0777, true);
            $output->writeln(
                '<info>+d</info> ' .
                str_replace($basePath, '', realpath($featuresPath)) .
                ' <comment>- place your *.feature files here</comment>'
            );
        }

        if (!is_dir($bootstrapPath)) {
            mkdir($bootstrapPath, 0777, true);
            $output->writeln(
                '<info>+d</info> ' .
                str_replace($basePath, '', realpath($bootstrapPath)) .
                ' <comment>- place bootstrap scripts and static files here</comment>'
            );

            file_put_contents(
                $bootstrapPath . DIRECTORY_SEPARATOR . 'FeatureContext.php',
                $this->getFeatureContextSkelet()
            );

            $output->writeln(
                '<info>+f</info> ' .
                str_replace($basePath, '', realpath($bootstrapPath)) . DIRECTORY_SEPARATOR .
                'FeatureContext.php <comment>- place your feature related code here</comment>'
            );
        }
    }

    /**
     * Returns feature context skelet.
     *
     * @return  string
     */
    protected function getFeatureContextSkelet()
    {
return <<<'PHP'
<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\Pending;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

//
// Place your definition and hook methods here:
//
//    /**
//     * @Given /^I have done something with "([^"]*)"$/
//     */
//    public function iHaveDoneSomethingWith($argument)
//    {
//        doSomethingWith($argument);
//    }
//
}

PHP;
    }
}
