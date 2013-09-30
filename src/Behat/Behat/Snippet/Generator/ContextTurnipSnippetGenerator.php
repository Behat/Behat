<?php

namespace Behat\Behat\Snippet\Generator;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Snippet\ContextSnippet;
use Behat\Behat\Snippet\SnippetInterface;
use Behat\Behat\Snippet\Util\Transliterator;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use ReflectionClass;

/**
 * Context turnip-style snippet generator.
 * Generates turnip snippets for friendly contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextTurnipSnippetGenerator implements GeneratorInterface
{
    /**
     * @var string[string]
     */
    private static $proposedMethods = array();

    /**
     * Checks if generator supports suite, contextPool and step.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contextPool
     * @param StepNode             $step
     *
     * @return Boolean
     */
    public function supports(SuiteInterface $suite, ContextPoolInterface $contextPool, StepNode $step)
    {
        if (!$contextPool->hasContexts()) {
            return false;
        }

        foreach ($contextPool->getContextClasses() as $class) {
            if (in_array('Behat\Behat\Snippet\Context\TurnipSnippetsFriendlyInterface', class_implements($class))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates snippet.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contextPool
     * @param StepNode             $step
     *
     * @return SnippetInterface
     */
    public function generate(SuiteInterface $suite, ContextPoolInterface $contextPool, StepNode $step)
    {
        $contextClass = null;
        foreach ($contextPool->getContextClasses() as $class) {
            if (in_array('Behat\Behat\Snippet\Context\TurnipSnippetsFriendlyInterface', class_implements($class))) {
                $contextClass = $class;
                break;
            }
        }

        $reflection = new ReflectionClass($contextClass);
        $replacePatterns = array(
            "/(?<=\s|^)\"[^\"]+\"(?=\s|$)/",
            "/(?<=\s|^)'[^']+'(?=\s|$)/",
            "/(?<=\s|^)\d+/"
        );

        $count = 0;
        $pattern = $text = $step->getText();
        foreach ($replacePatterns as $replacePattern) {
            $pattern = preg_replace_callback($replacePattern, function () use (&$count) {
                return ':arg' . ++$count;
            }, $pattern);
        }

        $methodName = preg_replace($replacePatterns, '', $text);
        $methodName = Transliterator::transliterate($methodName, ' ');
        $methodName = preg_replace('/[^a-zA-Z\_\ ]/', '', $methodName);
        $methodName = str_replace(' ', '', ucwords($methodName));

        if (0 !== strlen($methodName)) {
            $methodName[0] = strtolower($methodName[0]);
        } else {
            $methodName = 'stepDefinition1';
        }

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
            foreach (self::$proposedMethods[$contextClass] as $proposedRegex => $proposedMethod) {
                if ($proposedRegex !== $pattern) {
                    while ($proposedMethod === $methodName) {
                        $methodName = preg_replace('/\d+$/', '', $methodName);
                        $methodName .= $methodNumber++;
                    }
                }
            }
        }
        self::$proposedMethods[$contextClass][$pattern] = $methodName;

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

        $description = $this->generateSnippet($pattern, $methodName, $args);

        return new ContextSnippet($step->getType(), $description, array($contextClass));
    }

    protected function generateSnippet($regex, $methodName, array $args)
    {
        return sprintf(
            <<<PHP
    /**
     * @%s %s
     */
    public function %s(%s)
    {
        throw new PendingException();
    }
PHP
            ,
            '%s',
            str_replace('%', '%%', $regex),
            $methodName,
            implode(', ', $args)
        );
    }
}
