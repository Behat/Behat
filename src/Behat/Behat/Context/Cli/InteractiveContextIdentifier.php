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
     * Initialises identifier.
     */
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly InputInterface $input,
        private readonly OutputInterface $output,
    ) {
    }

    public function guessTargetContextClass(ContextEnvironment $environment): ?string
    {
        if (!$this->input->isInteractive()) {
            return null;
        }

        $suiteName = $environment->getSuite()->getName();
        $contextClasses = $environment->getContextClasses();

        if (!count($contextClasses)) {
            return null;
        }

        $message = $this->translator->trans('snippet_context_choice', ['%count%' => $suiteName], 'output');
        $choices = array_merge(['None'], $contextClasses);
        $default = '1';

        $answer = $this->askQuestion('>> ' . $message, $choices, $default);

        return 'None' !== $answer ? $answer : null;
    }

    /**
     * Asks user question.
     *
     * @param string[] $choices
     */
    private function askQuestion(string $message, array $choices, string $default): string
    {
        $this->output->writeln('');
        $helper = new QuestionHelper();
        $question = new ChoiceQuestion(' ' . $message . "\n", $choices, $default);

        $result = $helper->ask($this->input, $this->output, $question);
        assert(is_string($result), 'Answer should be a string - all choices were strings');

        return $result;
    }
}
