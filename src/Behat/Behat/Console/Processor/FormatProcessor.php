<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Formatter\FormatManager;

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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FormatProcessor implements ProcessorInterface
{
    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::configure()
     */
    public function configure(Command $command)
    {
        $defaultFormatters = FormatManager::getDefaultFormatterClasses();

        $command
            ->addOption('--format', '-f', InputOption::VALUE_REQUIRED,
                "How to format features. <comment>pretty</comment> is default.\n" .
                "Default formatters are:\n" .
                implode("\n",
                    array_map(function($name) use($defaultFormatters) {
                        $class = $defaultFormatters[$name];
                        return "- <comment>$name</comment>: " . $class::getDescription();
                    }, array_keys($defaultFormatters))
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
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::process()
     */
    public function process(ContainerInterface $container, InputInterface $input, OutputInterface $output)
    {
        $translator = $container->get('behat.translator');
        $manager    = $container->get('behat.format_manager');
        $locator    = $container->get('behat.path_locator');
        $formats    = array_map('trim', explode(',',
            $input->getOption('format') ?: $container->getParameter('behat.formatter.name')
        ));

        // load formatters translations
        foreach (require($container->getParameter('behat.paths.i18n')) as $lang => $messages) {
            $translator->addResource('array', $messages, $lang, 'behat');
        }

        // add user-defined formatter classes to manager
        foreach ($container->getParameter('behat.formatter.classes') as $name => $class) {
            $manager->setFormatterClass($name, $class);
        }

        // init specified for run formatters
        foreach ($formats as $format) {
            $manager->initFormatter($format);
        }

        // set formatter options from behat.yml
        foreach (($parameters = $container->getParameter('behat.formatter.parameters')) as $name => $value) {
            if ('output_path' === $name) {
                continue;
            }
            $manager->setFormattersParameter($name, $value);
        }

        $manager->setFormattersParameter('base_path', $locator->getWorkPath());
        $manager->setFormattersParameter('support_path', $locator->getBootstrapPath());
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
            $out = $locator->getOutputPath($outputs);

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

                $out = $locator->getOutputPath($out);

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

    /**
     * Returns correct value for input switch.
     *
     * @param InputInterface $input
     * @param string         $name
     *
     * return Boolean|null
     */
    protected function getSwitchValue(InputInterface $input, $name)
    {
        if ($input->getOption($name)) {
            return true;
        } elseif ($input->getOption('no-'.$name)) {
            return false;
        }

        return null;
    }
}
