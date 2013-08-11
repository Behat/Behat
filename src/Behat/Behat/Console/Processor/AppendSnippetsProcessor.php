<?php

namespace Behat\Behat\Console\Processor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Output\FormatterManager;
use Behat\Behat\Snippet\EventSubscriber\ContextSnippetsAppender;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Append snippets processor.
 * Appends snippets to appropriate context(s).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AppendSnippetsProcessor implements ProcessorInterface
{
    /**
     * @var ContextSnippetsAppender
     */
    private $contextSnippetsAppender;
    /**
     * @var FormatterManager
     */
    private $formatterManager;

    /**
     * Initializes processor.
     *
     * @param ContextSnippetsAppender $contextSnippetsAppender
     * @param FormatterManager        $formatterManager
     */
    public function __construct(
        ContextSnippetsAppender $contextSnippetsAppender,
        FormatterManager $formatterManager
    )
    {
        $this->contextSnippetsAppender = $contextSnippetsAppender;
        $this->formatterManager = $formatterManager;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command->addOption('--append-snippets', null, InputOption::VALUE_NONE,
            "Appends snippets for undefined steps into main context."
        );
    }

    /**
     * Processes data from console input.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('append-snippets')) {
            return;
        }

        $this->contextSnippetsAppender->enable();
        $this->formatterManager->setFormattersParameterIfExists('snippets', false);
    }

    /**
     * Returns priority of the processor in which it should be configured and executed.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 40;
    }
}
