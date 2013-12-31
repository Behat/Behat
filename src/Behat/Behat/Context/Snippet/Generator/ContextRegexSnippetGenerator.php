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
 * Context regex snippet generator.
 *
 * Generates regex snippets for regex-friendly contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextRegexSnippetGenerator implements SnippetGenerator
{
    /**
     * @var string[string]
     */
    private static $replacePatterns = array(
        "/(?<= |^)\\\'(?:((?!\\').)*)\\\'(?= |$)/" => "\\'([^\']*)\\'", // Single quoted strings
        '/(?<= |^)\"(?:[^\"]*)\"(?= |$)/'          => "\"([^\"]*)\"", // Double quoted strings
        '/(\d+)/'                                  => "(\\d+)", // Numbers
    );
    /**
     * @var string
     */
    private static $templateTemplate = <<<TPL
    /**
     * @%%s /^%s$/
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
     * Checks if generator supports environment and step.
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
     * Generates snippet from environment and step.
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

        $stepText = $this->getStepText($step);
        $stepRegex = $this->getStepRegex($stepText);
        $methodName = $this->getMethodName($contextClass, $stepText, $stepRegex);
        $methodArguments = $this->getMethodArguments($step, $stepRegex);
        $snippetTemplate = $this->getSnippetTemplate($stepRegex, $methodName, $methodArguments);

        return new ContextSnippet($step, $snippetTemplate, $contextClass);
    }

    /**
     * Generates snippet template using regex, method name and arguments.
     *
     * @param string   $regex
     * @param string   $methodName
     * @param string[] $methodArguments
     *
     * @return string
     */
    protected function getSnippetTemplate($regex, $methodName, array $methodArguments)
    {
        return sprintf(
            static::$templateTemplate,
            str_replace('%', '%%', $regex),
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
                'Behat\Behat\Context\RegexAcceptingContext',
                class_implements($class)
            )
            ) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Returns snippet-ready step text.
     *
     * @param StepNode $step
     *
     * @return string
     */
    private function getStepText(StepNode $step)
    {
        $text = $step->getText();
        $text = preg_replace('/([\/\[\]\(\)\\\^\$\.\|\?\*\+\'])/', '\\\\$1', $text);

        return $text;
    }

    /**
     * Generates definition regex from text.
     *
     * @param string $stepText
     *
     * @return string
     */
    private function getStepRegex($stepText)
    {
        return preg_replace(
            array_keys(static::$replacePatterns),
            array_values(static::$replacePatterns),
            $stepText
        );
    }

    /**
     * Generates method name using step text and regex.
     *
     * @param string $contextClass
     * @param string $stepText
     * @param string $stepRegex
     *
     * @return string
     */
    private function getMethodName($contextClass, $stepText, $stepRegex)
    {
        $methodName = $this->deduceMethodName($stepText);
        $methodName = $this->ensureMethodNameUniqueness($contextClass, $stepRegex, $methodName);

        return $methodName;
    }

    /**
     * Returns an array of method argument names from step and regex.
     *
     * @param StepNode $step
     * @param string   $stepRegex
     *
     * @return string[]
     */
    private function getMethodArguments(StepNode $step, $stepRegex)
    {
        preg_match('/^' . $stepRegex . '$/', $step->getText(), $matches);
        $count = count($matches) - 1;

        $args = array();
        for ($i = 0; $i < $count; $i++) {
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
        $methodName = preg_replace(array_keys(static::$replacePatterns), '', $stepText);
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
     * @param string $stepRegex
     * @param string $methodName
     *
     * @return string
     */
    private function ensureMethodNameUniqueness($contextClass, $stepRegex, $methodName)
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
            foreach (self::$proposedMethods[$contextClass] as $proposedRegex => $proposedMethod) {
                if ($proposedRegex !== $stepRegex) {
                    while ($proposedMethod === $methodName) {
                        $methodName = preg_replace('/\d+$/', '', $methodName);
                        $methodName .= $methodNumber++;
                    }
                }
            }
        }

        return static::$proposedMethods[$contextClass][$stepRegex] = $methodName;
    }
}
