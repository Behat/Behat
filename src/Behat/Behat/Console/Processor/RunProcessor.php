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
        ;
    }

    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::process()
     */
    public function process(ContainerInterface $container, InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('strict') || $container->getParameter('behat.options.strict')) {
            $container->get('behat.runner')->setStrict(true);
        } else {
            $container->get('behat.runner')->setStrict(false);
        }

        if ($input->getOption('dry-run') || $container->getParameter('behat.options.dry_run')) {
            $container->get('behat.runner')->setDryRun(true);
            $container->get('behat.hook_dispatcher')->setDryRun(true);
        } else {
            $container->get('behat.runner')->setDryRun(false);
            $container->get('behat.hook_dispatcher')->setDryRun(false);
        }
    }
}
