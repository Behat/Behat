<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet\Generator;

use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Behat\Context\Snippet\ContextSnippet;
use Behat\Behat\Snippet\Snippet;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Context turnip-style snippet generator.
 *
 * Generates turnip snippets for turnip-friendly contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextTurnipSnippetGenerator extends AbstractContextSnippetGenerator
{
    /**
     * @var string[]
     */
    private static $placeholderPatterns = array(
        "/(?<=\s|^)\"[^\"]+\"(?=\s|$)/",
        "/(?<=\s|^)'[^']+'(?=\s|$)/",
        "/(?<=\s|^)\d+/"
    );
    /**
     * @var string
     */
    private static $templateTemplate = <<<TPL
    /**
     * @%%s %s
     */
    public function %s(%s)
    {
        throw new PendingException();
    }
TPL;

    /**
     * Checks if generator supports search query.
     *
     * @param Environment $environment
     * @param StepNode    $step
     *
     * @return Boolean
     */
    public function supportsEnvironmentAndStep(Environment $environment, StepNode $step)
    {
        if (!$environment instanceof ContextEnvironment) {
            return false;
        }

        if (!$environment->hasContexts()) {
            return false;
        }

        return null !== $this->getMainContextClass($environment);
    }

    /**
     * Generates snippet from search.
     *
     * @param ContextEnvironment $environment
     * @param StepNode           $step
     *
     * @return Snippet
     */
    public function generateSnippet(Environment $environment, StepNode $step)
    {
        $contextClass = $this->getMainContextClass($environment);

        $stepText = $step->getText();
        list($stepPattern, $tokenCount) = $this->getPatternAndTokenCount($stepText);
        $methodName = $this->getMethodName($contextClass, $stepText, $stepPattern);
        $methodArguments = $this->getMethodArguments($step, $tokenCount);
        $snippetTemplate = $this->getSnippetTemplate($stepPattern, $methodName, $methodArguments);

        return new ContextSnippet($step, $snippetTemplate, $contextClass);
    }

    /**
     * Generates snippet template using regex, method name and arguments.
     *
     * @param string   $pattern
     * @param string   $methodName
     * @param string[] $methodArguments
     *
     * @return string
     */
    protected function getSnippetTemplate($pattern, $methodName, array $methodArguments)
    {
        return sprintf(
            self::$templateTemplate,
            str_replace('%', '%%', $pattern),
            $methodName,
            implode(', ', $methodArguments)
        );
    }

    /**
     * Returns replace patterns.
     *
     * @return array
     */
    protected function getReplacePatterns()
    {
        return self::$placeholderPatterns;
    }

    /**
     * Tries to get main context class out of the environment.
     *
     * @param ContextEnvironment $environment
     *
     * @return null|string
     */
    private function getMainContextClass(ContextEnvironment $environment)
    {
        foreach ($environment->getContextClasses() as $class) {
            if (in_array(
                'Behat\Behat\Context\TurnipAcceptingContext',
                class_implements($class)
            )
            ) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Generates definition pattern and counts tokens inside.
     *
     * @param $stepText
     *
     * @return array
     */
    private function getPatternAndTokenCount($stepText)
    {
        $count = 0;
        $pattern = $stepText;
        foreach ($this->getReplacePatterns() as $replacePattern) {
            $pattern = preg_replace_callback(
                $replacePattern,
                function () use (&$count) {
                    return ':arg' . ++$count;
                },
                $pattern
            );
        }

        return array($pattern, $count);
    }
}
