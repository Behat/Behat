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
 * Help (story-syntax and definition printers) processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HelpProcessor extends Processor
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
        $command
            ->addOption('--story-syntax', null, InputOption::VALUE_NONE,
                "Print <comment>*.feature</comment> example.\n" .
                "Use <info>--lang</info> to see specific language."
            )
            ->addOption('--definitions', '-d', InputOption::VALUE_REQUIRED,
                "Print all available step definitions:\n" .
                "- use <info>-dl</info> to just list definition expressions.\n" .
                "- use <info>-di</info> to show definitions with extended info.\n" .
                "- use <info>-d 'needle'</info> to find specific definitions.\n" .
                "Use <info>--lang</info> to see definitions in specific language.\n"
            )
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
        if ($input->getOption('story-syntax')) {
            $this->container->get('behat.help_printer.story_syntax')->printSyntax(
                $output, $input->getOption('lang') ?: 'en'
            );

            exit(0);
        }

        if ($type = $input->getOption('definitions')) {
            if ('l' === $type) {
                $short  = true;
                $search = null;
            } elseif ('i' === $type) {
                $short  = false;
                $search = null;
            } else {
                $short  = false;
                $search = $type;
            }

            $this->container->get('behat.help_printer.definitions')->printDefinitions(
                $output, $search, $input->getOption('lang') ?: 'en', $short
            );

            exit(0);
        }
    }
}
