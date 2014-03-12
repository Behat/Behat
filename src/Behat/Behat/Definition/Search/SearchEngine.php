<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Search;

use Behat\Behat\Definition\DefinitionFinder;
use Behat\Behat\Definition\SearchResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Searches for a step definition in a specific environment.
 *
 * @see DefinitionFinder
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SearchEngine
{
    /**
     * Searches for a step definition.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     *
     * @return null|SearchResult
     */
    public function searchDefinition(Environment $environment, FeatureNode $feature, StepNode $step);
}
