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
use Behat\Behat\Context\Pool\ContextPool;
use Behat\Behat\Context\Snippet\ContextSnippet;
use Behat\Behat\Snippet\Generator\SnippetGenerator;
use Behat\Behat\Snippet\Snippet;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Environment\Environment;
use Behat\Transliterator\Transliterator;
use ReflectionClass;

/**
 * Context turnip-style snippet generator.
 *
 * Generates turnip snippets for turnip-friendly contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextTurnipSnippetGenerator implements SnippetGenerator
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
     * @var string[string]
     */
    private static $proposedMethods = array();

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

        $contextPool = $environment->getContextPool();
        if (!$contextPool->hasContexts()) {
            return false;
        }

        return null !== $this->getMainContextClass($contextPool);
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
        $contextPool = $environment->getContextPool();
        $contextClass = $this->getMainContextClass($contextPool);

        $stepText = $step->getText();
        list($stepPattern, $tokenCount) = $this->getPatternAndTokensCount($stepText);
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
            static::$templateTemplate,
            str_replace('%', '%%', $pattern),
            $methodName,
            implode(', ', $methodArguments)
        );
    }

    /**
     * Tries to get main context class out of the pool.
     *
     * @param ContextPool $contextPool
     *
     * @return null|string
     */
    private function getMainContextClass(ContextPool $contextPool)
    {
        $contextClass = null;
        foreach ($contextPool->getContextClasses() as $class) {
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
    private function getPatternAndTokensCount($stepText)
    {
        $count = 0;
        $pattern = $stepText;
        foreach (static::$placeholderPatterns as $replacePattern) {
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

    /**
     * Generates method name using step text and pattern.
     *
     * @param string $contextClass
     * @param string $stepText
     * @param string $stepPattern
     *
     * @return string
     */
    private function getMethodName($contextClass, $stepText, $stepPattern)
    {
        $methodName = $this->deduceMethodName($stepText);
        $methodName = $this->ensureMethodNameUniqueness($contextClass, $stepPattern, $methodName);

        return $methodName;
    }

    /**
     * Returns an array of method argument names from step and token count.
     *
     * @param StepNode $step
     * @param integer  $tokenCount
     *
     * @return string[]
     */
    private function getMethodArguments(StepNode $step, $tokenCount)
    {
        $args = array();
        for ($i = 0; $i < $tokenCount; $i++) {
            $args[] = "\$arg" . ($i + 1);
        }

        foreach ($step->getArguments() as $argument) {
            if ($argument instanceof PyStringNode) {
                $args[] = "PyStringNode \$string";
            } elseif ($argument instanceof TableNode) {
                $args[] = "TableNode \$table";
            }
        }

        return $args;
    }

    /**
     * Generates definition method name based on the step text.
     *
     * @param string $stepText
     *
     * @return string
     */
    private function deduceMethodName($stepText)
    {
        $methodName = preg_replace(static::$placeholderPatterns, '', $stepText);
        $methodName = Transliterator::transliterate($methodName, ' ');
        $methodName = preg_replace('/[^a-zA-Z\_\ ]/', '', $methodName);
        $methodName = str_replace(' ', '', ucwords($methodName));

        // check that method name is not empty
        if (0 !== strlen($methodName)) {
            $methodName[0] = strtolower($methodName[0]);

            return $methodName;
        } else {
            $methodName = 'stepDefinition1';

            return $methodName;
        }
    }

    /**
     * Ensures uniqueness of the method name in the context.
     *
     * @param string $contextClass
     * @param string $stepPattern
     * @param string $methodName
     *
     * @return string
     */
    private function ensureMethodNameUniqueness($contextClass, $stepPattern, $methodName)
    {
        $reflection = new ReflectionClass($contextClass);

        // get method number from method name
        $methodNumber = 2;
        if (preg_match('/(\d+)$/', $methodName, $matches)) {
            $methodNumber = intval($matches[1]);
        }

        // check that proposed method name isn't already defined in context
        while ($reflection->hasMethod($methodName)) {
            $methodName = preg_replace('/\d+$/', '', $methodName);
            $methodName .= $methodNumber++;
        }

        // check that proposed method name haven't been proposed earlier
        if (isset(self::$proposedMethods[$contextClass])) {
            foreach (self::$proposedMethods[$contextClass] as $proposedPattern => $proposedMethod) {
                if ($proposedPattern !== $stepPattern) {
                    while ($proposedMethod === $methodName) {
                        $methodName = preg_replace('/\d+$/', '', $methodName);
                        $methodName .= $methodNumber++;
                    }
                }
            }
        }

        return static::$proposedMethods[$contextClass][$stepPattern] = $methodName;
    }
}
