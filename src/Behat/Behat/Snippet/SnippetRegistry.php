<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet;

use Behat\Behat\Context\Snippet\Generator\CannotGenerateStepPatternException;
use Behat\Behat\Snippet\Generator\SnippetGenerator;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Acts like a snippet repository by producing snippets from registered undefined steps using snippet generators.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SnippetRegistry implements SnippetRepository
{
    /**
     * @var SnippetGenerator[]
     */
    private $generators = [];
    /**
     * @var UndefinedStep[]
     */
    private $undefinedSteps = [];
    /**
     * @var AggregateSnippet[]
     */
    private $snippets = [];
    /**
     * @var bool
     */
    private $snippetsGenerated = false;

    /**
     * @var list<CannotGenerateStepPatternException>
     */
    private array $exceptions = [];

    /**
     * Registers snippet generator.
     */
    public function registerSnippetGenerator(SnippetGenerator $generator): void
    {
        $this->generators[] = $generator;
        $this->snippetsGenerated = false;
    }

    /**
     * Generates and registers snippet.
     */
    public function registerUndefinedStep(Environment $environment, StepNode $step): void
    {
        $this->undefinedSteps[] = new UndefinedStep($environment, $step);
        $this->snippetsGenerated = false;
    }

    /**
     * Returns all generated snippets.
     *
     * @return AggregateSnippet[]
     */
    public function getSnippets()
    {
        $this->generateSnippets();

        return $this->snippets;
    }

    /**
     * Returns steps for which there was no snippet generated.
     *
     * @return UndefinedStep[]
     */
    public function getUndefinedSteps()
    {
        $this->generateSnippets();

        return $this->undefinedSteps;
    }

    /**
     * @return list<CannotGenerateStepPatternException>
     */
    public function getGenerationFailures(): array
    {
        $this->generateSnippets();

        return $this->exceptions;
    }

    /**
     * Generates snippets for undefined steps.
     */
    private function generateSnippets(): void
    {
        if ($this->snippetsGenerated) {
            return;
        }

        $snippetsSet = [];
        foreach ($this->undefinedSteps as $i => $undefinedStep) {
            try {
                $snippet = $this->generateSnippet($undefinedStep->getEnvironment(), $undefinedStep->getStep());
            } catch (CannotGenerateStepPatternException $e) {
                $this->exceptions[] = $e;
                unset($this->undefinedSteps[$i]);
                continue;
            }

            if (!$snippet) {
                continue;
            }

            if (!isset($snippetsSet[$snippet->getHash()])) {
                $snippetsSet[$snippet->getHash()] = [];
            }

            $snippetsSet[$snippet->getHash()][] = $snippet;
            unset($this->undefinedSteps[$i]);
        }

        $this->snippets = array_values(
            array_map(
                fn (array $snippets): AggregateSnippet => new AggregateSnippet($snippets),
                $snippetsSet
            )
        );
        $this->undefinedSteps = array_values($this->undefinedSteps);
        $this->snippetsGenerated = true;
    }

    /**
     * @return Snippet|null
     */
    private function generateSnippet(Environment $environment, StepNode $step)
    {
        foreach ($this->generators as $generator) {
            if ($generator->supportsEnvironmentAndStep($environment, $step)) {
                return $generator->generateSnippet($environment, $step);
            }
        }

        return null;
    }
}
