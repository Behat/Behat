<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet\Generator;

use Behat\Behat\Snippet\Generator\SnippetGenerator;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Transliterator\Transliterator;
use ReflectionClass;

/**
 * Abstract context snippet generator.
 *
 * Generates snippets for a context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class AbstractContextSnippetGenerator implements SnippetGenerator
{
    /**
     * @var string[string]
     */
    private static $proposedMethods = array();

    /**
     * Returns replace patterns.
     *
     * @return array
     */
    abstract protected function getReplacePatterns();

    /**
     * Generates method name using step text and regex.
     *
     * @param string $contextClass
     * @param string $stepText
     * @param string $stepPattern
     *
     * @return string
     */
    final protected function getMethodName($contextClass, $stepText, $stepPattern)
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
    final protected function getMethodArguments(StepNode $step, $tokenCount)
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
        $methodName = preg_replace($this->getReplacePatterns(), '', $stepText);
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

        // check that proposed method name isn't already defined in the context
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

        return self::$proposedMethods[$contextClass][$stepPattern] = $methodName;
    }
}
