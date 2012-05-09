<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Formatter\FormatterDispatcher;

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
        $translator = $this->container->get('behat.translator');
        $manager    = $this->container->get('behat.formatter.manager');
        $formats    = array_map('trim', explode(',',
            $input->getOption('format') ?: $this->container->getParameter('behat.formatter.name')
        ));

        // load formatters translations
        foreach (require($this->container->getParameter('behat.paths.i18n')) as $lang => $messages) {
            $translator->addResource('array', $messages, $lang, 'behat');
        }

        // add user-defined formatter classes to manager
        foreach ($this->container->getParameter('behat.formatter.classes') as $name => $class) {
            $manager->addDispatcher(new FormatterDispatcher($name, $class));
        }

        // init specified for run formatters
        foreach ($formats as $format) {
            $manager->initFormatter($format);
        }

        // set formatter options from behat.yml
        foreach (($parameters = $this->container->getParameter('behat.formatter.parameters')) as $name => $value) {
            if ('output_path' === $name) {
                continue;
            }
            $manager->setFormattersParameter($name, $value);
        }

        $manager->setFormattersParameter('base_path', $this->container->getParameter('behat.paths.base'));
        $manager->setFormattersParameter('support_path', $this->container->getParameter('behat.paths.bootstrap'));
        $manager->setFormattersParameter('decorated', $output->isDecorated());

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

        if ($input->getOption('out')) {
            $outputs = $input->getOption('out');
        } elseif (isset($parameters['output_path'])) {
            $outputs = $parameters['output_path'];
        } else {
            return;
        }

        if (false === strpos($outputs, ',')) {
            $out = $this->container->getParameter('behat.paths.base').DIRECTORY_SEPARATOR.$outputs;

            // get realpath
            if (!file_exists($out)) {
                touch($out);
                $out = realpath($out);
                unlink($out);
            } else {
                $out = realpath($out);
            }

            $manager->setFormattersParameter('output_path', $out);
            $manager->setFormattersParameter('decorated', (bool) $this->getSwitchValue($input, 'ansi'));
        } else {
            foreach (array_map('trim', explode(',', $outputs)) as $i => $out) {
                if (!$out || 'null' === $out || 'false' === $out) {
                    continue;
                }

                $out = $this->container->getParameter('behat.paths.base').DIRECTORY_SEPARATOR.$out;

                // get realpath
                if (!file_exists($out)) {
                    touch($out);
                    $out = realpath($out);
                    unlink($out);
                } else {
                    $out = realpath($out);
                }

                $formatters = $manager->getFormatters();
                if (isset($formatters[$i])) {
                    $formatters[$i]->setParameter('output_path', $out);
                    $formatters[$i]->setParameter('decorated', (bool) $this->getSwitchValue($input, 'ansi'));
                }
            }
        }
    }
}
