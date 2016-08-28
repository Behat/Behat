<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Cli;

use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Behat\Context\Snippet\Generator\ContextSnippetGenerator;
use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Configures which context snippets are generated for.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextSnippetsController implements Controller
{
    /**
     * @var ContextSnippetGenerator
     */
    private $generator;

    /**
     * Initialises controller.
     */
    public function __construct(ContextSnippetGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(SymfonyCommand $command)
    {
        $command
            ->addOption(
                '--snippets-for', null, InputOption::VALUE_OPTIONAL,
                "Specifies which context class to generate snippets for."
            )
            ->addOption(
                '--snippets-type', null, InputOption::VALUE_REQUIRED,
                "Specifies which type of snippets (turnip, regex) to generate."
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption('--snippets-for')) {
            $this->generator->setContextClassGetter($this->createContextClassGetter($input, $output));
        }

        if (null !== $input->getOption('snippets-type')) {
            $this->generator->setSnippetsType($input->getOption('snippets-type'));
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return \Closure
     */
    private function createContextClassGetter(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getOption('snippets-for')) {
            return function () use ($input) {
                return $input->getOption('snippets-for');
            };
        }

        return function (ContextEnvironment $environment) use ($input, $output) {
            $output->writeln('');
            $helper = new QuestionHelper();
            $question = new ChoiceQuestion(
                sprintf(
                    ' <snippet_undefined><snippet_keyword>%s</snippet_keyword> suite has undefined steps. ' .
                    'Please choose the context to generate snippets:</snippet_undefined>' . "\n",
                    $environment->getSuite()->getName()),
                $environment->getContextClasses(),
                current($environment->getContextClasses())
            );

            return $helper->ask($input, $output, $question);
        };
    }
}
