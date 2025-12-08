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
    private $engines = [];

    /**
     * Registers definition search engine.
     */
    public function registerSearchEngine(SearchEngine $searchEngine): void
    {
        $this->engines[] = $searchEngine;
    }

    /**
     * Searches definition for a provided step in a provided environment.
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
