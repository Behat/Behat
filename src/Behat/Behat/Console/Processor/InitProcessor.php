<?php

namespace Behat\Behat\Console\Processor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Suite\Event\SuitesCarrierEvent;
use Behat\Behat\Suite\GherkinSuite;
use RuntimeException;
use Symfony\Component\ClassLoader\ClassLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Init operation processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class InitProcessor extends DispatchingService implements ProcessorInterface
{
    /**
     * @var ClassLoader
     */
    private $autoloader;
    /**
     * @var string
     */
    private $basePath;

    /**
     * Initializes processor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ClassLoader              $autoloader
     * @param string                   $basePath
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ClassLoader $autoloader, $basePath)
    {
        parent::__construct($eventDispatcher);

        $this->autoloader = $autoloader;
        $this->basePath = $basePath;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command->addOption('--init', null, InputOption::VALUE_NONE,
            "Create <comment>features</comment> directory structure."
        );
    }

    /**
     * Processes data from container and console input.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null|integer
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('init')) {
            return null;
        }

        $suitesProvider = new SuitesCarrierEvent();
        $this->dispatch(EventInterface::LOAD_SUITES, $suitesProvider);

        $basePath = $this->basePath . DIRECTORY_SEPARATOR;
        foreach ($suitesProvider->getSuites() as $suite) {
            if ($suite instanceof GherkinSuite) {
                foreach ($suite->getFeatureLocators() as $locator) {
                    if (0 !== strpos($locator, '@') && !is_dir($path = $this->locatePath($locator))) {
                        mkdir($path, 0777, true);

                        $output->writeln('<info>+d</info> ' .
                            str_replace($basePath, '', realpath($path)) .
                            ' <comment>- place your *.feature files here</comment>'
                        );
                    }
                }
            }

            foreach ($suite->getContextClasses() as $class) {
                if (class_exists($class)) {
                    continue;
                }

                $path = $this->findClassFile($class);
                if (!is_dir(dirname($path))) {
                    mkdir(dirname($path), 0777, true);
                }

                file_put_contents($path, $this->getFeatureContextSkelet($class));

                $output->writeln(
                    '<info>+f</info> ' .
                    str_replace($basePath, '', realpath($path)) .
                    ' <comment>- place your definitions here</comment>'
                );
            }
        }

        return 0;
    }

    /**
     * Returns priority of the processor in which it should be configured and executed.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 90;
    }

    /**
     * Returns feature context skelet.
     *
     * @param string $class
     *
     * @return string
     */
    protected function getFeatureContextSkelet($class)
    {
        $namespace = '';
        $className = $class;
        if (false !== $pos = strrpos($class, '\\')) {
            $namespace = 'namespace ' . substr($class, 0, $pos) . ';' . PHP_EOL . PHP_EOL;
            $className = substr($class, $pos + 1);
        }

        return strtr(<<<'PHP'
<?php

{namespace}use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Behat context class.
 */
class {className} implements ContextInterface
{
    /**
     * Initializes context. Every scenario gets it's own context object.
     *
     * @param array $parameters Suite parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
    }
}

PHP
            , array(
                '{namespace}' => $namespace,
                '{className}' => $className,
            )
        );
    }

    /**
     * Finds file to store a class.
     *
     * @param string $class
     *
     * @return string
     *
     * @throws RuntimeException If class file could not be determined
     */
    private function findClassFile($class)
    {
        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        } else {
            // PEAR-like class name
            $classPath = null;
            $className = $class;
        }

        $classPath .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        foreach ($this->autoloader->getPrefixes() as $prefix => $dirs) {
            if (0 === strpos($class, $prefix)) {
                return current($dirs) . DIRECTORY_SEPARATOR . $classPath;
            }
        }

        if ($dirs = $this->autoloader->getFallbackDirs()) {
            return current($dirs) . DIRECTORY_SEPARATOR . $classPath;
        }

        throw new RuntimeException(sprintf(
            'Could not find where to put "%s" class. Have you configured autoloader properly?'
        ));
    }

    /**
     * Locates path from a relative one.
     *
     * @param string $path
     *
     * @return string
     */
    private function locatePath($path)
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return $this->basePath . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @return Boolean
     */
    private function isAbsolutePath($file)
    {
        if ($file[0] == '/' || $file[0] == '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] == ':'
                && ($file[2] == '\\' || $file[2] == '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ) {
            return true;
        }

        return false;
    }
}
