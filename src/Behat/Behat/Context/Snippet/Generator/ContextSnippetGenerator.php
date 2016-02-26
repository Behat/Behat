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
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Snippet\Exception\EnvironmentSnippetGenerationException;
use Behat\Behat\Snippet\Generator\SnippetGenerator;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Environment\Environment;
use ReflectionClass;

/**
 * Generates snippets for a context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextSnippetGenerator implements SnippetGenerator
{
    /**
     * @var string[string]
     */
    private static $proposedMethods = array();
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
     * @var PatternTransformer
     */
    private $patternTransformer;

    /**
     * Initializes snippet generator.
     *
     * @param PatternTransformer $patternTransformer
     */
    public function __construct(PatternTransformer $patternTransformer)
    {
        $this->patternTransformer = $patternTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEnvironmentAndStep(Environment $environment, StepNode $step)
    {
        if (!$environment instanceof ContextEnvironment) {
            return false;
        }

        if (!$environment->hasContexts()) {
            return false;
        }

        return null !== $this->getSnippetAcceptingContextClass($environment);
    }

    /**
     * {@inheritdoc}
     */
    public function generateSnippet(Environment $environment, StepNode $step)
    {
        if (!$environment instanceof ContextEnvironment) {
            throw new EnvironmentSnippetGenerationException(sprintf(
                'ContextSnippetGenerator does not support `%s` environment.',
                get_class($environment)
            ), $environment);
        }

        $contextClass = $this->getSnippetAcceptingContextClass($environment);
        $patternType = $this->getPatternType($contextClass);
        $stepText = $step->getText();
        $pattern = $this->patternTransformer->generatePattern($patternType, $stepText);

        $methodName = $this->getMethodName($contextClass, $pattern->getCanonicalText(), $pattern->getPattern());
        $methodArguments = $this->getMethodArguments($step, $pattern->getPlaceholderCount());
        $snippetTemplate = $this->getSnippetTemplate($pattern->getPattern(), $methodName, $methodArguments);

        $usedClasses = $this->getUsedClasses($step);

        return new ContextSnippet($step, $snippetTemplate, $contextClass, $usedClasses);
    }

    /**
     * Returns snippet-accepting context class.
     *
     * @param ContextEnvironment $environment
     *
     * @return null|string
     */
    private function getSnippetAcceptingContextClass(ContextEnvironment $environment)
    {
        foreach ($environment->getContextClasses() as $class) {
            if (in_array('Behat\Behat\Context\SnippetAcceptingContext', class_implements($class))) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Returns snippet-type that provided context class accepts.
     *
     * @param string $contextClass
     *
     * @return null|string
     */
    private function getPatternType($contextClass)
    {
        if (!in_array('Behat\Behat\Context\CustomSnippetAcceptingContext', class_implements($contextClass))) {
            return null;
        }

        return $contextClass::getAcceptedSnippetType();
    }

    /**
     * Generates method name using step text and regex.
     *
     * @param string $contextClass
     * @param string $canonicalText
     * @param string $pattern
     *
     * @return string
     */
    private function getMethodName($contextClass, $canonicalText, $pattern)
    {
        $methodName = $this->deduceMethodName($canonicalText);
        $methodName = $this->getUniqueMethodName($contextClass, $pattern, $methodName);

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
            $args[] = '$arg' . ($i + 1);
        }

        foreach ($step->getArguments() as $argument) {
            $args[] = $this->getMethodArgument($argument);
        }

        return $args;
    }

    /**
     * Returns an array of classes used by the snippet template
     *
     * @param StepNode $step
     *
     * @return string[]
     */
    private function getUsedClasses(StepNode $step)
    {
        $usedClasses = array('Behat\Behat\Tester\Exception\PendingException');

        foreach ($step->getArguments() as $argument) {
            if ($argument instanceof TableNode) {
                $usedClasses[] = 'Behat\Gherkin\Node\TableNode';
            } elseif ($argument instanceof PyStringNode) {
                $usedClasses[] = 'Behat\Gherkin\Node\PyStringNode';
            }
        }

        return $usedClasses;
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
    private function getSnippetTemplate($pattern, $methodName, array $methodArguments)
    {
        return sprintf(
            self::$templateTemplate,
            str_replace('%', '%%', $pattern),
            $methodName,
            implode(', ', $methodArguments)
        );
    }

    /**
     * Generates definition method name based on the step text.
     *
     * @param string $canonicalText
     *
     * @return string
     */
    private function deduceMethodName($canonicalText)
    {
        // check that method name is not empty
        if (0 !== strlen($canonicalText)) {
            $canonicalText[0] = strtolower($canonicalText[0]);

            return $canonicalText;
        }

        return 'stepDefinition1';
    }

    /**
     * Ensures uniqueness of the method name in the context.
     *
     * @param string $contextClass
     * @param string $stepPattern
     * @param string $name
     *
     * @return string
     */
    private function getUniqueMethodName($contextClass, $stepPattern, $name)
    {
        $reflection = new ReflectionClass($contextClass);

        $number = $this->getMethodNumberFromTheMethodName($name);
        list($name, $number) = $this->getMethodNameNotExistentInContext($reflection, $name, $number);
        $name = $this->getMethodNameNotProposedEarlier($contextClass, $stepPattern, $name, $number);

        return $name;
    }

    /**
     * Tries to deduct method number from the provided method name.
     *
     * @param string $methodName
     *
     * @return integer
     */
    private function getMethodNumberFromTheMethodName($methodName)
    {
        $methodNumber = 2;
        if (preg_match('/(\d+)$/', $methodName, $matches)) {
            $methodNumber = intval($matches[1]);
        }

        return $methodNumber;
    }

    /**
     * Tries to guess method name that is not yet defined in the context class.
     *
     * @param ReflectionClass $reflection
     * @param string          $methodName
     * @param integer         $methodNumber
     *
     * @return array
     */
    private function getMethodNameNotExistentInContext(ReflectionClass $reflection, $methodName, $methodNumber)
    {
        while ($reflection->hasMethod($methodName)) {
            $methodName = preg_replace('/\d+$/', '', $methodName);
            $methodName .= $methodNumber++;
        }

        return array($methodName, $methodNumber);
    }

    /**
     * Tries to guess method name that is not yet proposed to the context class.
     *
     * @param string  $contextClass
     * @param string  $stepPattern
     * @param string  $name
     * @param integer $number
     *
     * @return string
     */
    private function getMethodNameNotProposedEarlier($contextClass, $stepPattern, $name, $number)
    {
        foreach ($this->getAlreadyProposedMethods($contextClass) as $proposedPattern => $proposedMethod) {
            if ($proposedPattern === $stepPattern) {
                continue;
            }

            while ($proposedMethod === $name) {
                $name = preg_replace('/\d+$/', '', $name);
                $name .= $number++;
            }
        }

        $this->markMethodAsAlreadyProposed($contextClass, $stepPattern, $name);

        return $name;
    }

    /**
     * Returns already proposed method names.
     *
     * @param string $contextClass
     *
     * @return string[]
     */
    private function getAlreadyProposedMethods($contextClass)
    {
        return isset(self::$proposedMethods[$contextClass]) ? self::$proposedMethods[$contextClass] : array();
    }

    /**
     * Marks method as proposed one.
     *
     * @param string $contextClass
     * @param string $stepPattern
     * @param string $methodName
     */
    private function markMethodAsAlreadyProposed($contextClass, $stepPattern, $methodName)
    {
        self::$proposedMethods[$contextClass][$stepPattern] = $methodName;
    }

    /**
     * Returns method argument.
     *
     * @param string $argument
     *
     * @return string
     */
    private function getMethodArgument($argument)
    {
        $arg = '__unknown__';
        if ($argument instanceof PyStringNode) {
            $arg = 'PyStringNode $string';
        } elseif ($argument instanceof TableNode) {
            $arg = 'TableNode $table';
        }

        return $arg;
    }
}
