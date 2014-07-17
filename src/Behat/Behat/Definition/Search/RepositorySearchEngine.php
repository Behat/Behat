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
     * @var ArgumentOrganiser
     */
    private $argumentOrganiser;

    /**
     * Initializes search engine.
     *
     * @param DefinitionRepository $repository
     * @param PatternTransformer   $patternTransformer
     * @param DefinitionTranslator $translator
     * @param ArgumentOrganiser    $argumentOrganiser
     */
    public function __construct(
        DefinitionRepository $repository,
        PatternTransformer $patternTransformer,
        DefinitionTranslator $translator,
        ArgumentOrganiser $argumentOrganiser
    ) {
        $this->repository = $repository;
        $this->patternTransformer = $patternTransformer;
        $this->translator = $translator;
        $this->argumentOrganiser = $argumentOrganiser;
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
        $multi = $step->getArguments();

        $definitions = array();
        $result = null;

        foreach ($this->repository->getEnvironmentDefinitions($environment) as $definition) {
            $definition = $this->translator->translateDefinition($suite, $definition, $language);

            if (!$newResult = $this->match($definition, $stepText, $multi)) {
                continue;
            }

            $result = $newResult;
            $definitions[] = $newResult->getMatchedDefinition();
        }

        if (count($definitions) > 1) {
            throw new AmbiguousMatchException($result->getMatchedText(), $definitions);
        }

        return $result;
    }

    /**
     * Attempts to match provided definition against a step text.
     *
     * @param Definition          $definition
     * @param string              $stepText
     * @param ArgumentInterface[] $multiline
     *
     * @return null|SearchResult
     */
    private function match(Definition $definition, $stepText, array $multiline)
    {
        $regex = $this->patternTransformer->transformPatternToRegex($definition->getPattern());

        if (!preg_match($regex, $stepText, $match)) {
            return null;
        }

        $function = $definition->getReflection();
        $match = array_merge($match, array_values($multiline));
        $arguments = $this->argumentOrganiser->organiseArguments($function, $match);

        return new SearchResult($definition, $stepText, $arguments);
    }
}
