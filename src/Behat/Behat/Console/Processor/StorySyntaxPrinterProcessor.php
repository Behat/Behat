<?php

namespace Behat\Behat\Console\Processor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Gherkin\Support\SyntaxPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Story syntax printer processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StorySyntaxPrinterProcessor implements ProcessorInterface
{
    /**
     * @var SyntaxPrinter
     */
    private $syntaxPrinter;

    /**
     * Constructs processor.
     *
     * @param SyntaxPrinter $syntaxPrinter
     */
    public function __construct(SyntaxPrinter $syntaxPrinter)
    {
        $this->syntaxPrinter = $syntaxPrinter;
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
                "Print <comment>*.feature</comment> example." . PHP_EOL .
                "Use <info>--lang</info> to see specific language."
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
        if (!$input->getOption('story-syntax')) {
            return null;
        }

        $this->syntaxPrinter->printSyntax($output, $input->getOption('lang') ? : 'en');

        return 0;
    }

    /**
     * Returns priority of the processor in which it should be configured and executed.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 80;
    }
}
