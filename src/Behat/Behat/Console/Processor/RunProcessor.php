<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Runner configuration processor.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RunProcessor implements ProcessorInterface
{
    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::configure()
     */
    public function configure(Command $command)
    {
        $command
            ->addOption('--strict', null, InputOption::VALUE_NONE,
                'Fail if there are any undefined or pending steps.'
            )
            ->addOption('--dry-run', null, InputOption::VALUE_NONE,
                'Invokes formatters without executing the steps & hooks.'
            )
            ->addOption('--rerun', null, InputOption::VALUE_REQUIRED,
                "Save list of failed scenarios into new file\n" .
                "or use existing file to run only scenarios from it."
            )
            ->addOption('--append-snippets', null, InputOption::VALUE_NONE,
                "Appends snippets for undefined steps into main context."
            )
        ;
    }

    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::process()
     */
    public function process(ContainerInterface $container, InputInterface $input, OutputInterface $output)
    {
        $runner         = $container->get('behat.runner');
        $hookDispatcher = $container->get('behat.hook_dispatcher');

        $runner->setStrict(
            $input->getOption('strict') || $container->getParameter('behat.options.strict')
        );
        $runner->setDryRun(
            $input->getOption('dry-run') || $container->getParameter('behat.options.dry_run')
        );
        $hookDispatcher->setDryRun(
            $input->getOption('dry-run') || $container->getParameter('behat.options.dry_run')
        );

        if ($file = $input->getOption('rerun') ?: $container->getParameter('behat.options.rerun')) {
            if (file_exists($file)) {
                $runner->setFeaturesPaths(explode("\n", trim(file_get_contents($file))));
            }

            $container->get('behat.format_manager')
                ->initFormatter('failed')
                ->setParameter('output_path', $file);
        }

        if ($input->getOption('append-snippets') || $container->getParameter('behat.options.append_snippets')) {
            $contextRefl = new \ReflectionClass(
                $container->get('behat.context_dispatcher')->getContextClass()
            );

            if ($contextRefl->implementsInterface('Behat\Behat\Context\ClosuredContextInterface')) {
                throw new \RuntimeException(
                    '--append-snippets doesn\'t support closured contexts'
                );
            }

            $formatManager = $container->get('behat.format_manager');
            $formatManager->setFormattersParameter('snippets', false);

            $formatter = $formatManager->initFormatter('snippets');
            $formatter->setParameter('decorated', false);
            $formatter->setParameter('output_decorate', false);
            $formatter->setParameter('output', $snippets = fopen('php://memory', 'rw'));

            $container->get('behat.event_dispatcher')
                ->addListener('afterSuite', function() use($contextRefl, $snippets) {
                    rewind($snippets);
                    $snippets = stream_get_contents($snippets);

                    if (trim($snippets)) {
                        $snippets = strtr($snippets, array('\\' => '\\\\', '$' => '\\$'));
                        $context  = file_get_contents($contextRefl->getFileName());
                        $context  = preg_replace('/}[ \n]*$/', rtrim($snippets)."\n}\n", $context);

                        file_put_contents($contextRefl->getFileName(), $context);
                    }
                }, -5);
        }
    }
}
