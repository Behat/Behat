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
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Initializes search engine.
     *
     * @param DefinitionRepository $repository
     * @param PatternTransformer   $patternTransformer
     * @param TranslatorInterface  $translator
     */
    public function __construct(
        DefinitionRepository $repository,
        PatternTransformer $patternTransformer,
        TranslatorInterface $translator
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
    public function searchDefinition(Environment $environment, FeatureNode $feature, StepNode $step)
    {
        $language = $feature->getLanguage();
        $stepText = $step->getText();
        $stepArgs = $step->getArguments();

        $definitions = array();
        $result = null;

        foreach ($this->repository->getEnvironmentDefinitions($environment) as $definition) {
            if (!preg_match($this->getRegex($environment, $definition, $language), $stepText, $match)) {
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
     * Returns definition regex.
     *
     * @param Environment $environment
     * @param Definition  $definition
     * @param string      $language
     *
     * @return string
     */
    private function getRegex(Environment $environment, Definition $definition, $language)
    {
        $assetsId = $environment->getSuite()->getName();
        $pattern = $definition->getPattern();

        $translatedPattern = $this->translator->trans($pattern, array(), $assetsId, $language);
        if ($pattern == $translatedPattern) {
            return $this->patternTransformer->transformPatternToRegex($pattern);
        }

        return $this->patternTransformer->transformPatternToRegex($translatedPattern);
    }

    /**
     * Prepares definition arguments.
     *
     * @param Definition $definition
     * @param array      $match
     * @param array      $multiline
     *
     * @return array
     */
    private function getArguments(Definition $definition, array $match, array $multiline)
    {
        $arguments = array();
        foreach ($definition->getReflection()->getParameters() as $num => $parameter) {
            if (isset($match[$parameter->getName()])) {
                $arguments[$parameter->getName()] = $match[$parameter->getName()];
            } elseif (isset($match[$num])) {
                $arguments[$num] = $match[$num];
            }
        }

        foreach ($multiline as $argument) {
            $arguments[] = $argument;
        }

        return $arguments;
    }
}
