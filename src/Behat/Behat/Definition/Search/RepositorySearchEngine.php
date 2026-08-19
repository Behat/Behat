<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Search;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\DefinitionRepository;
use Behat\Behat\Definition\Exception\AmbiguousMatchException;
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Definition\Translator\DefinitionTranslator;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\DataTable;
use Behat\Step\DocString;
use Behat\Testwork\Argument\ArgumentOrganiser;
use Behat\Testwork\Argument\Exception\UnexpectedMultilineArgumentException;
use Behat\Testwork\Deprecation\DeprecationCollector;
use Behat\Testwork\Environment\Environment;
use ReflectionFunctionAbstract;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * Searches for a step definition using definition repository.
 *
 * @see DefinitionRepository
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RepositorySearchEngine implements SearchEngine
{
    /**
     * Initializes search engine.
     */
    public function __construct(
        private readonly DefinitionRepository $repository,
        private readonly PatternTransformer $patternTransformer,
        private readonly DefinitionTranslator $translator,
        private readonly ArgumentOrganiser $argumentOrganiser,
    ) {
    }

    /**
     * @throws AmbiguousMatchException
     */
    public function searchDefinition(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
    ): ?SearchResult {
        $suite = $environment->getSuite();
        $language = $feature->getLanguage();
        $stepText = $step->getText();
        $multi = $step->getArguments();
        $stepLocation = $feature->getFile().':'.$step->getLine();

        $definitions = [];
        $result = null;

        foreach ($this->repository->getEnvironmentDefinitions($environment) as $definition) {
            $definition = $this->translator->translateDefinition($suite, $definition, $language);
            $newResult = $this->match($definition, $stepLocation, $stepText, $multi);
            if (!$newResult instanceof SearchResult) {
                continue;
            }

            $result = $newResult;
            $matchedDefinition = $newResult->getMatchedDefinition();
            if ($matchedDefinition instanceof Definition) {
                $definitions[] = $matchedDefinition;
            }
        }

        if (count($definitions) > 1) {
            throw new AmbiguousMatchException($result->getMatchedText(), $definitions);
        }

        return $result;
    }

    /**
     * Attempts to match provided definition against a step text.
     *
     * @param ArgumentInterface[] $multiline
     */
    private function match(Definition $definition, string $stepLocation, string $stepText, array $multiline): ?SearchResult
    {
        $match = $this->patternTransformer->matchPattern($definition->getPattern(), $stepText);
        if ($match === false) {
            return null;
        }

        $function = $definition->getReflection();
        $match = array_merge($match, $this->wrapMultilineArguments($function, array_values($multiline)));

        try {
            $arguments = $this->argumentOrganiser->organiseArguments($function, $match);
        } catch (UnexpectedMultilineArgumentException $e) {
            // Add the location of the feature and step that caused the problem.
            // We can't do this where the exception is originally thrown because the ArgumentOrganiser interface
            // is used for other types of function / argument processing e.g. Context constructors, so it has no
            // knowledge of the concept of a Step.
            throw new UnexpectedMultilineArgumentException(
                $e->getMessage() . PHP_EOL . 'This is probably an error in your step implementation or in ' . $stepLocation,
                code: $e->getCode(),
                previous: $e
            );
        }

        $this->checkForUnusedArguments($definition, $match);

        return new SearchResult($definition, $stepText, $arguments);
    }

    /**
     * Reports a deprecation if the pattern provides more arguments than the definition can accept.
     *
     * @param array<int|string, mixed> $match the pattern match, with any multiline arguments appended
     */
    private function checkForUnusedArguments(Definition $definition, array $match): void
    {
        $function = $definition->getReflection();

        if ($function->isVariadic()) {
            return;
        }

        $providedCount = $this->countProvidedArguments($match);
        $parameterCount = $function->getNumberOfParameters();

        if ($providedCount <= $parameterCount) {
            return;
        }

        DeprecationCollector::trigger(sprintf(
            'The pattern "%s" provides %d argument%s but %s only accepts %d. '
            . 'Silently discarding the extra argument%s is deprecated and will be an error in Behat 4.0: '
            . 'either add the missing parameters or use non-capturing groups "(?:...)" in the pattern.',
            $definition->getPattern(),
            $providedCount,
            $providedCount === 1 ? '' : 's',
            $definition->getPath(),
            $parameterCount,
            $providedCount - $parameterCount === 1 ? '' : 's',
        ), $function->getFileName() ?: null, $function->getStartLine() ?: null);
    }

    /**
     * Counts the arguments a pattern match will provide to the definition.
     *
     * The first element of a `preg_match` result is the full match rather than an argument, and every
     * named capturing group is represented twice - once under its name and once under its number.
     *
     * @param array<int|string, mixed> $match
     */
    private function countProvidedArguments(array $match): int
    {
        $namedGroups = count(array_filter(array_keys($match), is_string(...)));

        return count($match) - 1 - $namedGroups;
    }

    /**
     * Wraps multiline arguments into their dedicated Behat representation
     * when the definition asks for it.
     *
     * The raw Gherkin node always wins: an argument is only wrapped when no
     * parameter of the definition accepts the node itself, and some parameter
     * accepts the wrapper.
     *
     * @param list<ArgumentInterface> $multiline
     *
     * @return list<ArgumentInterface|DataTable|DocString>
     */
    private function wrapMultilineArguments(ReflectionFunctionAbstract $function, array $multiline): array
    {
        return array_map(
            function (ArgumentInterface $argument) use ($function) {
                if ($argument instanceof TableNode
                    && !$this->someParameterAccepts($function, $argument::class)
                    && $this->someParameterAccepts($function, DataTable::class)
                ) {
                    return new DataTable($argument);
                }

                if ($argument instanceof PyStringNode
                    && !$this->someParameterAccepts($function, $argument::class)
                    && $this->someParameterAccepts($function, DocString::class)
                ) {
                    return new DocString($argument);
                }

                return $argument;
            },
            $multiline,
        );
    }

    /**
     * @param class-string $class
     */
    private function someParameterAccepts(ReflectionFunctionAbstract $function, string $class): bool
    {
        foreach ($function->getParameters() as $parameter) {
            $type = $parameter->getType();
            $namedTypes = match (true) {
                $type instanceof ReflectionNamedType => [$type],
                $type instanceof ReflectionUnionType,
                $type instanceof ReflectionIntersectionType => array_filter(
                    $type->getTypes(),
                    static fn ($member) => $member instanceof ReflectionNamedType,
                ),
                default => [],
            };

            foreach ($namedTypes as $namedType) {
                if (!$namedType->isBuiltin() && is_a($class, $namedType->getName(), true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
