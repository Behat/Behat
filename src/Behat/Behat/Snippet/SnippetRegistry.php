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
 * Snippet registry.
 *
 * Acts like a snippet repository by producing snippets from registered undefined steps using snippet generators.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SnippetRegistry implements SnippetRepository
{
    /**
     * @var SnippetGenerator[]
     */
    private $generators = array();
    /**
     * @var array
     */
    private $undefinedStepTuples = array();
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
        $this->undefinedStepTuples[] = array($environment, $step);
        $this->snippetsGenerated = false;
    }

    /**
     * Returns all generated snippets.
     *
     * @return AggregateSnippet[]
     */
    public function getSnippets()
    {
        if ($this->snippetsGenerated) {
            return $this->snippets;
        }

        $snippetsSet = array();
        foreach ($this->undefinedStepTuples as $tuple) {
            list($environment, $step) = $tuple;
            $snippet = $this->generateSnippet($environment, $step);

            if (!$snippet) {
                continue;
            }

            if (!isset($snippetsSet[$snippet->getHash()])) {
                $snippetsSet[$snippet->getHash()] = array();
            }

            $snippetsSet[$snippet->getHash()][] = $snippet;
        }

        $this->snippetsGenerated = true;

        return $this->snippets = array_values(array_map(
            function (array $snippets) {
                return new AggregateSnippet($snippets);
            },
            $snippetsSet
        ));
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
