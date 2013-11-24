<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Formatter\FormatterManager,
    Behat\Behat\Formatter\FormatterDispatcher;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Format processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FormatProcessor extends Processor
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
        $formatDispatchers = $this->container->get('behat.formatter.manager')->getDispatchers();

        $command
            ->addOption('--format', '-f', InputOption::VALUE_REQUIRED,
                "How to format features. <comment>pretty</comment> is default.\n" .
                "Default formatters are:\n" .
                implode("\n",
                    array_map(function($dispatcher) {
                        $comment = '- <comment>'.$dispatcher->getName().'</comment>: ';

                        if ($dispatcher->getDescription()) {
                            $comment .= $dispatcher->getDescription();
                        } else {
                            $comment .= $dispatcher->getClass();
                        }

                        return $comment;
                    }, $formatDispatchers)
                ) . "\n" .
                "Can use multiple formats at once (splitted with \"<comment>,</comment>\")"
            )
            ->addOption('--out', null, InputOption::VALUE_REQUIRED,
                "Write formatter output to a file/directory\n" .
                "instead of STDOUT <comment>(output_path)</comment>."
            )
            ->addOption('--lang', null, InputOption::VALUE_REQUIRED,
                'Print formatter output in particular language.'
            )

            // --[no-]ansi
            ->addOption('--ansi', null, InputOption::VALUE_NONE,
                "Whether or not to use ANSI color in the output.\n".
                "Behat decides based on your platform and the output\n".
                "destination if not specified."
            )
            ->addOption('--no-ansi', null, InputOption::VALUE_NONE)

            // --[no-]time
            ->addOption('--time', null, InputOption::VALUE_NONE,
                "Whether or not to show timer in output."
            )
            ->addOption('--no-time', null, InputOption::VALUE_NONE)

            // --[no-]paths
            ->addOption('--paths', null, InputOption::VALUE_NONE,
                "Whether or not to print sources paths."
            )
            ->addOption('--no-paths', null, InputOption::VALUE_NONE)

            // --[no-]snippets
            ->addOption('--snippets', null, InputOption::VALUE_NONE,
                "Whether or not to print snippets for undefined steps."
            )
            ->addOption('--no-snippets', null, InputOption::VALUE_NONE)

            // --[no-]snippets-paths
            ->addOption('--snippets-paths', null, InputOption::VALUE_NONE,
                "Whether or not to print details about undefined steps\n".
                "in their snippets."
            )
            ->addOption('--no-snippets-paths', null, InputOption::VALUE_NONE)

            // --[no-]multiline
            ->addOption('--multiline', null, InputOption::VALUE_NONE,
                "Whether or not to print multiline arguments for steps."
            )
            ->addOption('--no-multiline', null, InputOption::VALUE_NONE)

            // --[no-]expand
            ->addOption('--expand', null, InputOption::VALUE_NONE,
                "Whether or not to expand scenario outline examples\n".
                "tables.\n"
            )
            ->addOption('--no-expand', null, InputOption::VALUE_NONE)
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
        $manager = $this->container->get('behat.formatter.manager');
        $formats = array_map('trim', explode(',',
            $input->getOption('format') ?: $this->container->getParameter('behat.formatter.name')
        ));

        $this->loadFormatterTranslations();
        $this->loadCustomFormatters($manager);
        $this->initFormatters($manager, $formats);
        $this->configureFormatters($manager, $input, $output);
        $this->initMultipleOutputs($manager, $input);
    }

    /**
     * Loads formatter translations from behat.paths.i18n parameter file.
     */
    protected function loadFormatterTranslations()
    {
        if (!is_file($i18nFile = $this->container->getParameter('behat.paths.i18n'))) {
            return;
        }

        $translator = $this->container->get('behat.translator');
        foreach (require($i18nFile) as $lang => $messages) {
            $translator->addResource('array', $messages, $lang, 'behat');
        }
    }

    /**
     * Loads custom formatters, defined in behat.yml.
     *
     * @param FormatterManager $manager
     */
    protected function loadCustomFormatters(FormatterManager $manager)
    {
        foreach ($this->container->getParameter('behat.formatter.classes') as $name => $class) {
            $manager->addDispatcher(new FormatterDispatcher($class, $name));
        }
    }

    /**
     * Inits formatters.
     *
     * @param FormatterManager $manager
     * @param $array           $formats
     */
    protected function initFormatters(FormatterManager $manager, array $formats)
    {
        foreach ($formats as $format) {
            $manager->initFormatter($format);
        }
    }

    /**
     * Configures formatters based on container, input and output configurations.
     *
     * @param FormatterManager $manager
     * @param InputInterface   $input
     * @param OutputInterface  $output
     */
    protected function configureFormatters(FormatterManager $manager, InputInterface $input,
                                           OutputInterface $output)
    {
        $parameters = $this->container->getParameter('behat.formatter.parameters');
        foreach ($parameters as $name => $value) {
            if ('output_path' === $name) {
                continue;
            }
            $manager->setFormattersParameter($name, $value);
        }

        $manager->setFormattersParameter('base_path',
            $this->container->getParameter('behat.paths.base')
        );
        $manager->setFormattersParameter('features_path',
            $this->container->getParameter('behat.paths.features')
        );
        $manager->setFormattersParameter('support_path',
            $this->container->getParameter('behat.paths.bootstrap')
        );
        $manager->setFormattersParameter('decorated',
            $output->isDecorated()
        );

        if ($input->getOption('verbose')) {
            $manager->setFormattersParameter('verbose', true);
        }

        if ($input->getOption('lang')) {
            $manager->setFormattersParameter('language', $input->getOption('lang'));
        }

        if (null !== $ansi = $this->getSwitchValue($input, 'ansi')) {
            $output->setDecorated($ansi);
            $manager->setFormattersParameter('decorated', $ansi);
        }
        if (null !== $time = $this->getSwitchValue($input, 'time')) {
            $manager->setFormattersParameter('time', $time);
        }
        if (null !== $snippets = $this->getSwitchValue($input, 'snippets')) {
            $manager->setFormattersParameter('snippets', $snippets);
        }
        if (null !== $snippetsPaths = $this->getSwitchValue($input, 'snippets-paths')) {
            $manager->setFormattersParameter('snippets_paths', $snippetsPaths);
        }
        if (null !== $paths = $this->getSwitchValue($input, 'paths')) {
            $manager->setFormattersParameter('paths', $paths);
        }
        if (null !== $expand = $this->getSwitchValue($input, 'expand')) {
            $manager->setFormattersParameter('expand', $expand);
        }
        if (null !== $multiline = $this->getSwitchValue($input, 'multiline')) {
            $manager->setFormattersParameter('multiline_arguments', $multiline);
        }
    }

    /**
     * Initializes multiple formatters with different outputs.
     *
     * @param FormatterManager $manager
     * @param InputInterface   $input
     */
    protected function initMultipleOutputs(FormatterManager $manager, InputInterface $input)
    {
        $parameters = $this->container->getParameter('behat.formatter.parameters');
        if ($input->getOption('out')) {
            $outputs = $input->getOption('out');
        } elseif (isset($parameters['output_path'])) {
            $outputs = $parameters['output_path'];
        } else {
            return;
        }

        if (false === strpos($outputs, ',')) {
            $outputPath = $this->locateOutputPath($outputs);
            $manager->setFormattersParameter('output_path', $outputPath);
            $manager->setFormattersParameter('decorated', (bool) $this->getSwitchValue($input, 'ansi'));

            return;
        }

        foreach (array_map('trim', explode(',', $outputs)) as $i => $out) {
            if (!$out || 'null' === $out || 'false' === $out) {
                continue;
            }

            $outputPath = $this->locateOutputPath($out);
            $formatters = $manager->getFormatters();
            if (isset($formatters[$i])) {
                $formatters[$i]->setParameter('output_path', $outputPath);
                $formatters[$i]->setParameter('decorated', (bool) $this->getSwitchValue($input, 'ansi'));
            }
        }
    }

    /**
     * Locates output path from relative one.
     *
     * @param string $out
     *
     * @return string
     */
    private function locateOutputPath($out)
    {
        if ($this->isAbsolutePath($out)) {
            return $out;
        }

        $out = getcwd().DIRECTORY_SEPARATOR.$out;

        if (!file_exists($out)) {
            touch($out);
            $out = realpath($out);
            unlink($out);
        } else {
            $out = realpath($out);
        }

        return $out;
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
