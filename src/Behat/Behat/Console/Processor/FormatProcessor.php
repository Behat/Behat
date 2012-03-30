<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Formatter\FormatManager,
    Behat\Behat\Console\Input\InputSwitch;

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
        ;

        $definition = $command->getDefinition();

        $definition->addOption(new InputSwitch('--[no-]ansi',
            "Whether or not to use ANSI color in the output.\n".
            "Behat decides based on your platform and the output\n".
            "destination if not specified."
        ));
        $definition->addOption(new InputSwitch('--[no-]time',
            "Whether or not to show timer in output."
        ));
        $definition->addOption(new InputSwitch('--[no-]paths',
            "Whether or not to print sources paths."
        ));
        $definition->addOption(new InputSwitch('--[no-]snippets',
            "Whether or not to print snippets for undefined steps."
        ));
        $definition->addOption(new InputSwitch('--[no-]snippets-paths',
            "Whether or not to print details about undefined steps\n".
            "in their snippets."
        ));
        $definition->addOption(new InputSwitch('--[no-]multiline',
            "Whether or not to print multiline arguments for steps."
        ));
        $definition->addOption(new InputSwitch('--[no-]expand',
            "Whether or not to expand scenario outline examples\n".
            "tables.\n"
        ));
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

        if (null !== $ansi = $input->getOption('[no-]ansi')) {
            $output->setDecorated($ansi);
            $manager->setFormattersParameter('decorated', $ansi);
        }
        if (null !== $time = $input->getOption('[no-]time')) {
            $manager->setFormattersParameter('time', $time);
        }
        if (null !== $snippets = $input->getOption('[no-]snippets')) {
            $manager->setFormattersParameter('snippets', $snippets);
        }
        if (null !== $snippetsPaths = $input->getOption('[no-]snippets-paths')) {
            $manager->setFormattersParameter('snippets_paths', $snippetsPaths);
        }
        if (null !== $paths = $input->getOption('[no-]paths')) {
            $manager->setFormattersParameter('paths', $paths);
        }
        if (null !== $expand = $input->getOption('[no-]expand')) {
            $manager->setFormattersParameter('expand', $expand);
        }
        if (null !== $multiline = $input->getOption('[no-]multiline')) {
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
            $manager->setFormattersParameter('decorated', (bool) $input->getOption('[no-]ansi'));
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
                    $formatters[$i]->setParameter('decorated', (bool) $input->getOption('[no-]ansi'));
                }
            }
        }
    }
}
