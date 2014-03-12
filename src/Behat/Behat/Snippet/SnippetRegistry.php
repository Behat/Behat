<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet;

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
    private $generators = array();
    /**
     * @var UndefinedStep[]
     */
    private $undefinedSteps = array();
    /**
     * @var AggregateSnippet[]
     */
    private $snippets = array();
    /**
     * @var Boolean
     */
    private $snippetsGenerated = false;

    /**
     * Registers snippet generator.
     *
     * @param SnippetGenerator $generator
     */
    public function registerSnippetGenerator(SnippetGenerator $generator)
    {
        $this->generators[] = $generator;
        $this->snippetsGenerated = false;
    }

    /**
     * Generates and registers snippet.
     *
     * @param Environment $environment
     * @param StepNode    $step
     *
     * @return null|Snippet
     */
    public function registerUndefinedStep(Environment $environment, StepNode $step)
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
     * Generates snippets for undefined steps.
     */
    private function generateSnippets()
    {
        if ($this->snippetsGenerated) {
            return null;
        }

        $snippetsSet = array();
        foreach ($this->undefinedSteps as $i => $undefinedStep) {
            $snippet = $this->generateSnippet($undefinedStep->getEnvironment(), $undefinedStep->getStep());

            if (!$snippet) {
                continue;
            }

            if (!isset($snippetsSet[$snippet->getHash()])) {
                $snippetsSet[$snippet->getHash()] = array();
            }

            $snippetsSet[$snippet->getHash()][] = $snippet;
            unset($this->undefinedSteps[$i]);
        }

        $this->snippets = array_values(
            array_map(
                function (array $snippets) {
                    return new AggregateSnippet($snippets);
                },
                $snippetsSet
            )
        );
        $this->undefinedSteps = array_values($this->undefinedSteps);
        $this->snippetsGenerated = true;
    }

    /**
     * @param Environment $environment
     * @param StepNode    $step
     *
     * @return null|Snippet
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
