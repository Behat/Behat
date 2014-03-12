<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition;

use Behat\Behat\Definition\Search\SearchEngine;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Finds specific step definition in environment using registered search engines.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DefinitionFinder
{
    /**
     * @var SearchEngine[]
     */
    private $engines = array();

    /**
     * Registers definition search engine.
     *
     * @param SearchEngine $searchEngine
     */
    public function registerSearchEngine(SearchEngine $searchEngine)
    {
        $this->engines[] = $searchEngine;
    }

    /**
     * Searches definition for a provided step in a provided environment.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     *
     * @return SearchResult
     */
    public function findDefinition(Environment $environment, FeatureNode $feature, StepNode $step)
    {
        foreach ($this->engines as $engine) {
            $result = $engine->searchDefinition($environment, $feature, $step);

            if (null !== $result && $result->hasMatch()) {
                return $result;
            }
        }

        return new SearchResult();
    }
}
