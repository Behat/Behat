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
use Behat\Behat\Definition\Call as DefinitionCall;
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Snippet\Exception\EnvironmentSnippetGenerationException;
use Behat\Behat\Snippet\Generator\SnippetGenerator;
use Behat\Behat\Snippet\Snippet;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
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
     * @var array<string, array<string, string>>
     */
    private static array $proposedMethods = [];

    private static string $snippetTemplate = <<<TPL
    #[%%s('%s')]
    public function %s(%s): void
    {
        throw new PendingException();
    }
TPL;

    private PatternTransformer $patternTransformer;

    private TargetContextIdentifier $contextIdentifier;

    private PatternIdentifier $patternIdentifier;

    /**
     * Initializes snippet generator.
     */
    public function __construct(PatternTransformer $patternTransformer)
    {
        $this->patternTransformer = $patternTransformer;

        $this->setContextIdentifier(new FixedContextIdentifier());
        $this->setPatternIdentifier(new FixedPatternIdentifier());
    }

    /**
     * Sets target context identifier.
     */
    public function setContextIdentifier(TargetContextIdentifier $identifier): void
    {
        $this->contextIdentifier = new CachedContextIdentifier($identifier);
    }

    /**
     * Sets target pattern type identifier.
     */
    public function setPatternIdentifier(PatternIdentifier $identifier): void
    {
        $this->patternIdentifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEnvironmentAndStep(Environment $environment, StepNode $step): bool
    {
        if (!$environment instanceof ContextEnvironment) {
            return false;
        }

        if (!$environment->hasContexts()) {
            return false;
        }

        return null !== $this->contextIdentifier->guessTargetContextClass($environment);
    }

    /**
     * {@inheritdoc}
     */
    public function generateSnippet(Environment $environment, StepNode $step): Snippet
    {
        if (!$environment instanceof ContextEnvironment) {
            throw new EnvironmentSnippetGenerationException(sprintf(
                'ContextSnippetGenerator does not support `%s` environment.',
                get_class($environment)
            ), $environment);
        }

        $contextClass = $this->contextIdentifier->guessTargetContextClass($environment);
        $patternType = $this->patternIdentifier->guessPatternType($contextClass);
        $stepText = $step->getText();
        $pattern = $this->patternTransformer->generatePattern($patternType, $stepText);

        $methodName = $this->getMethodName($contextClass, $pattern->getCanonicalText(), $pattern->getPattern());
        $methodArguments = $this->getMethodArguments($step, $pattern->getPlaceholderCount());
        $snippetTemplate = $this->getSnippetTemplate($pattern->getPattern(), $methodName, $methodArguments);

        $usedClasses = $this->getUsedClasses($step);

        return new ContextSnippet($step, $snippetTemplate, $contextClass, $usedClasses);
    }

    /**
     * Generates method name using step text and regex.
     */
    private function getMethodName(string $contextClass, string $canonicalText, string $pattern): string
    {
        $methodName = $this->deduceMethodName($canonicalText);

        return $this->getUniqueMethodName($contextClass, $pattern, $methodName);
    }

    /**
     * Returns an array of method argument names from step and token count.
     *
     * @return string[]
     */
    private function getMethodArguments(StepNode $step, int $tokenCount): array
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
     * @return string[]
     */
    private function getUsedClasses(StepNode $step): array
    {
        $usedClasses = [PendingException::class];

        $usedClasses[] = match ($step->getKeywordType()) {
            DefinitionCall\Given::KEYWORD => Given::class,
            DefinitionCall\When::KEYWORD => When::class,
            DefinitionCall\Then::KEYWORD => Then::class,
        };

        foreach ($step->getArguments() as $argument) {
            $usedClasses[] = match (true) {
                $argument instanceof TableNode => TableNode::class,
                $argument instanceof PyStringNode => PyStringNode::class,
            };
        }

        return $usedClasses;
    }

    /**
     * Generates snippet template using regex, method name and arguments.
     *
     * @param string[] $methodArguments
     */
    private function getSnippetTemplate(string $pattern, string $methodName, array $methodArguments): string
    {
        return sprintf(
            self::$snippetTemplate,
            $this->preparePattern($pattern),
            $methodName,
            implode(', ', $methodArguments)
        );
    }

    private function preparePattern(string $pattern): string
    {
        $pattern = str_replace('%', '%%', $pattern);
        $pattern = str_replace('\\\\', '\\\\\\\\', $pattern);
        return str_replace("'", "\'", $pattern);
    }

    /**
     * Generates definition method name based on the step text.
     */
    private function deduceMethodName(string $canonicalText): string
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
     */
    private function getUniqueMethodName(string $contextClass, string $stepPattern, string $name): string
    {
        $reflection = new ReflectionClass($contextClass);
        $number = $this->getMethodNumberFromTheMethodName($name);
        list($name, $number) = $this->getMethodNameNotExistentInContext($reflection, $name, $number);

        return $this->getMethodNameNotProposedEarlier($contextClass, $stepPattern, $name, $number);
    }

    /**
     * Tries to deduct method number from the provided method name.
     */
    private function getMethodNumberFromTheMethodName(string $methodName): int
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
     * @return array{string, int}
     */
    private function getMethodNameNotExistentInContext(ReflectionClass $reflection, string $methodName, int $methodNumber): array
    {
        while ($reflection->hasMethod($methodName)) {
            $methodName = preg_replace('/\d+$/', '', $methodName);
            $methodName .= $methodNumber++;
        }

        return [$methodName, $methodNumber];
    }

    /**
     * Tries to guess method name that is not yet proposed to the context class.
     */
    private function getMethodNameNotProposedEarlier(string $contextClass, string $stepPattern, string $name, int $number): string
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
     * @return array<string, string>
     */
    private function getAlreadyProposedMethods(string $contextClass): array
    {
        return self::$proposedMethods[$contextClass] ?? [];
    }

    /**
     * Marks method as proposed one.
     */
    private function markMethodAsAlreadyProposed(string $contextClass, string $stepPattern, string $methodName): void
    {
        self::$proposedMethods[$contextClass][$stepPattern] = $methodName;
    }

    private function getMethodArgument(ArgumentInterface $argument): string
    {
        return match (true) {
            $argument instanceof PyStringNode => 'PyStringNode $string',
            $argument instanceof TableNode => 'TableNode $table',
            default => '__unknown__',
        };
    }
}
