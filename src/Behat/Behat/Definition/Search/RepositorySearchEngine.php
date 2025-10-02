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
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Argument\ArgumentOrganiser;
use Behat\Testwork\Argument\Exception\UnexpectedMultilineArgumentException;
use Behat\Testwork\Environment\Environment;

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
    ) {
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
        $match = array_merge($match, array_values($multiline));

        try {
            $arguments = $this->argumentOrganiser->organiseArguments($function, $match);
        } catch (UnexpectedMultilineArgumentException $e) {
            // Add the location of the feature and step that caused the problem.
            // We can't do this where the exception is originally thrown because the ArgumentOrganiser interface
            // is used for other types of function / argument processing e.g. Context constructors, so it has no
            // knowledge of the concept of a Step.
            throw new UnexpectedMultilineArgumentException(
                $e->getMessage() . PHP_EOL . 'This is probably an error in your step implementation or in ' . $stepLocation,
                previous: $e
            );
        }

        return new SearchResult($definition, $stepText, $arguments);
    }
}
