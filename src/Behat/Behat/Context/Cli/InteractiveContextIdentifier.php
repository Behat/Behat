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
use Behat\Behat\Context\Snippet\Generator\TargetContextIdentifier;
use Behat\Behat\Definition\Translator\TranslatorInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Interactive identifier that asks user for input.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class InteractiveContextIdentifier implements TargetContextIdentifier
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var InputInterface
     */
    private $input;
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Initialises identifier.
     *
     * @param TranslatorInterface $translator
     * @param InputInterface      $input
     * @param OutputInterface     $output
     */
    public function __construct(TranslatorInterface $translator, InputInterface $input, OutputInterface $output)
    {
        $this->translator = $translator;
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function guessTargetContextClass(ContextEnvironment $environment)
    {
        if ($this->interactionIsNotSupported()) {
            return null;
        }

        $suiteName = $environment->getSuite()->getName();
        $contextClasses = $environment->getContextClasses();

        if (!count($contextClasses)) {
            return null;
        }

        $message = $this->translator->trans('snippet_context_choice', array('%count%' => $suiteName), 'output');
        $choices = array_values(array_merge(array('None'), $contextClasses));
        $default = 1;

        $answer = $this->askQuestion('>> ' . $message, $choices, $default);

        return 'None' !== $answer ? $answer : null;
    }

    /**
     * Asks user question.
     *
     * @param string   $message
     * @param string[] $choices
     * @param string   $default
     *
     * @return string
     */
    private function askQuestion($message, $choices, $default)
    {
        $this->output->writeln('');
        $helper = new QuestionHelper();
        $question = new ChoiceQuestion(' ' . $message . "\n", $choices, $default);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Checks if interactive mode is supported.
     *
     * @return bool
     *
     * @deprecated there is a better way to do it - `InputInterface::isInteractive()` method.
     *             Sadly, this doesn't work properly prior Symfony\Console 2.7 and as we need
     *             to support 2.5+ until the next major, we are forced to do a more explicit
     *             check for the CLI option. This should be reverted back to proper a
     *             `InputInterface::isInteractive()` call as soon as we bump dependencies
     *             to Symfony\Console 3.x in Behat 4.x.
     */
    private function interactionIsNotSupported()
    {
        return $this->input->hasParameterOption('--no-interaction');
    }
}
