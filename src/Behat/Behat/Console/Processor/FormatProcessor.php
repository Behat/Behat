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
        $defaultLanguage   = null;
        if (($locale = getenv('LANG')) && preg_match('/^([a-z]{2})/', $locale, $matches)) {
            $defaultLanguage = $matches[1];
        }

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
            ->addOption('--colors', null, InputOption::VALUE_NONE,
                'Force Behat to use ANSI color in the output.'
            )
            ->addOption('--no-colors', null, InputOption::VALUE_NONE,
                'Do not use ANSI color in the output.'
            )
            ->addOption('--no-time', null, InputOption::VALUE_NONE,
                'Hide time in output.'
            )
            ->addOption('--lang', null, InputOption::VALUE_REQUIRED,
                'Print formatter output in particular language.',
                $defaultLanguage
            )
            ->addOption('--no-paths', null, InputOption::VALUE_NONE,
                'Do not print the definition path with the steps.'
            )
            ->addOption('--no-snippets', null, InputOption::VALUE_NONE,
                'Do not print snippets for undefined steps.'
            )
            ->addOption('--snippets-paths', null, InputOption::VALUE_NONE,
                'Print snippets details about steps interested in them.'
            )
            ->addOption('--no-multiline', null, InputOption::VALUE_NONE,
                "No multiline arguments in output."
            )
            ->addOption('--expand', null, InputOption::VALUE_NONE,
                "Expand Scenario Outline Tables in output.\n"
            )
        ;
    }

    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::process()
     */
    public function process(ContainerInterface $container, InputInterface $input, OutputInterface $output)
    {
        $manager = $container->get('behat.format_manager');
        $locator = $container->get('behat.path_locator');
        $formats = array_map('trim', explode(',',
            $input->getOption('format') ?: $container->getParameter('behat.formatter.name')
        ));

        foreach ($formats as $format) {
            $manager->initFormatter($format);
        }

        $manager->setFormattersParameter('base_path', $locator->getWorkPath());
        $manager->setFormattersParameter('support_path', $locator->getBootstrapPath());
        $manager->setFormattersParameter('decorated', $output->isDecorated());

        $parameters = $container->getParameter('behat.formatter.parameters');
        foreach ($parameters as $param => $value) {
            $manager->setFormattersParameter($param, $value);
        }

        if ($input->getOption('verbose')) {
            $manager->setFormattersParameter('verbose', true);
        }

        if ($input->getOption('lang')) {
            $manager->setFormattersParameter('language', $input->getOption('lang'));
        }

        if ($input->getOption('colors')) {
            $output->setDecorated(true);
            $manager->setFormattersParameter('decorated', true);
        } elseif ($input->getOption('no-colors')) {
            $output->setDecorated(false);
            $manager->setFormattersParameter('decorated', false);
        }

        if ($input->getOption('no-time')) {
            $manager->setFormattersParameter('time', false);
        }

        if ($input->getOption('no-snippets')) {
            $manager->setFormattersParameter('snippets', false);
        }

        if ($input->getOption('snippets-paths')) {
            $manager->setFormattersParameter('snippets_paths', true);
        }

        if ($input->getOption('no-paths')) {
            $manager->setFormattersParameter('paths', false);
        }

        if ($input->getOption('expand')) {
            $manager->setFormattersParameter('expand', true);
        }

        if ($input->getOption('no-multiline')) {
            $manager->setFormattersParameter('multiline_arguments', false);
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
            $manager->setFormattersParameter('decorated', (bool) $input->getOption('colors'));
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
                    $formatters[$i]->setParameter('decorated', (bool) $input->getOption('colors'));
                }
            }
        }
    }
}
