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
use Behat\Behat\Definition\Exception\UnknownParameterValueException;
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Definition\Translator\DefinitionTranslator;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use ReflectionParameter;

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
     * @var DefinitionRepository
     */
    private $repository;
    /**
     * @var PatternTransformer
     */
    private $patternTransformer;
    /**
     * @var DefinitionTranslator
     */
    private $translator;

    /**
     * Initializes search engine.
     *
     * @param DefinitionRepository $repository
     * @param PatternTransformer   $patternTransformer
     * @param DefinitionTranslator $translator
     */
    public function __construct(
        DefinitionRepository $repository,
        PatternTransformer $patternTransformer,
        DefinitionTranslator $translator
    ) {
        $this->repository = $repository;
        $this->patternTransformer = $patternTransformer;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AmbiguousMatchException
     */
    public function searchDefinition(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step
    ) {
        $suite = $environment->getSuite();
        $language = $feature->getLanguage();
        $stepText = $step->getText();
        $stepArgs = $step->getArguments();

        $definitions = array();
        $result = null;

        foreach ($this->repository->getEnvironmentDefinitions($environment) as $definition) {
            $definition = $this->translator->translateDefinition($suite, $definition, $language);
            $regex = $this->patternTransformer->transformPatternToRegex($definition->getPattern());

            if (!preg_match($regex, $stepText, $match)) {
                continue;
            }

            $definitions[] = $definition;
            $arguments = $this->getArguments($definition, array_slice($match, 1), $stepArgs);

            $result = new SearchResult($definition, $stepText, $arguments);
        }

        if (count($definitions) > 1) {
            throw new AmbiguousMatchException($result->getMatchedText(), $definitions);
        }

        return $result;
    }

    /**
     * Prepares definition arguments.
     *
     * @param Definition          $definition
     * @param string[]            $match
     * @param ArgumentInterface[] $multiline
     *
     * @return mixed[]
     *
     * @throws UnknownParameterValueException
     */
    private function getArguments(Definition $definition, array $match, array $multiline)
    {
        $parameters = $definition->getReflection()->getParameters();

        $arguments = $this->getMatchedArguments($match, $parameters);
        $arguments = $this->appendMultilineArguments($multiline, $parameters, $arguments);

        $this->validateArguments($definition, $parameters, $arguments);

        return $arguments;
    }

    /**
     * Returns reflection parameter names.
     *
     * @param ReflectionParameter[] $parameters
     *
     * @return string[]
     */
    private function getParameterNames(array $parameters)
    {
        return array_map(
            function (ReflectionParameter $parameter) {
                return $parameter->getName();
            },
            $parameters
        );
    }

    /**
     * Returns array of matched arguments.
     *
     * @param array                 $match
     * @param ReflectionParameter[] $parameters
     *
     * @return mixed[]
     */
    private function getMatchedArguments(array $match, array $parameters)
    {
        $arguments = array();

        $names = $this->getParameterNames($parameters);
        list($numArguments, $nameArguments) = $this->splitNumberedAndNamedArguments(
            $match,
            $names
        );

        foreach ($parameters as $num => $parameter) {
            $name = $parameter->getName();

            if (isset($nameArguments[$name])) {
                $arguments[$name] = $nameArguments[$name];
            } elseif (isset($numArguments[$num])) {
                $arguments[$num] = $numArguments[$num];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $arguments[$num] = $parameter->getDefaultValue();
            }
        }

        return $arguments;
    }

    /**
     * Appends multiline arguments to the end of the arguments list.
     *
     * @param ArgumentInterface[]   $multiline
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $arguments
     *
     * @return mixed[]
     */
    private function appendMultilineArguments(
        array $multiline,
        array $parameters,
        array $arguments
    ) {
        foreach (array_values($multiline) as $num => $argument) {
            $arguments[count($parameters) - 1 - $num] = $argument;
        }

        return $arguments;
    }

    /**
     * Validates that all arguments are in place, throws exception otherwise.
     *
     * @param Definition            $definition
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $arguments
     *
     * @throws UnknownParameterValueException
     */
    private function validateArguments(Definition $definition, array $parameters, array $arguments)
    {
        foreach ($parameters as $num => $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($num, $arguments) || array_key_exists($name, $arguments)) {
                continue;
            }

            throw new UnknownParameterValueException(sprintf(
                'Can not find a matching value for an argument `%s` of the method `%s`.',
                $name,
                $definition->getPath()
            ));
        }
    }

    /**
     * Splits matches into two separate arrays - numbered and named.
     *
     * `preg_match` matches named arguments with named indexes and also
     * represents all arguments with numbered indexes. This method splits
     * that one array into two independent ones.
     *
     * @param array $match
     * @param array $parameterNames
     *
     * @return array
     */
    private function splitNumberedAndNamedArguments(array $match, array $parameterNames)
    {
        $numberedArguments = $match;
        $namedArguments = array();

        foreach ($match as $key => $val) {
            if (!is_integer($key) && in_array($key, $parameterNames)) {
                $namedArguments[$key] = $val;

                unset($numberedArguments[$key]);
                unset($numberedArguments[array_search($val, $numberedArguments)]);
            }
        }

        return array($numberedArguments, $namedArguments);
    }
}
