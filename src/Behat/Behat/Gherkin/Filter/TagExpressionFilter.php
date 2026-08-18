<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Filter;

use Behat\Gherkin\Filter\ComplexFilter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Cucumber\TagExpressions\Expression;
use Cucumber\TagExpressions\TagExpressionException;
use Cucumber\TagExpressions\TagExpressionParser;

/**
 * Filters scenarios by feature/scenario tag using a Cucumber tag expression.
 *
 * @see https://cucumber.io/docs/cucumber/api/#tag-expressions
 */
final class TagExpressionFilter extends ComplexFilter
{
    private readonly Expression $expression;

    /**
     * @throws TagExpressionException When the expression cannot be parsed
     */
    public function __construct(string $expressionString)
    {
        $this->expression = TagExpressionParser::parse($expressionString);
    }

    public function filterFeature(FeatureNode $feature): FeatureNode
    {
        $scenarios = [];
        foreach ($feature->getScenarios() as $scenario) {
            if (!$this->isScenarioMatch($feature, $scenario)) {
                continue;
            }

            if ($scenario instanceof OutlineNode && $scenario->hasExamples()) {
                $exampleTables = [];

                foreach ($scenario->getExampleTables() as $exampleTable) {
                    if ($this->evaluateTags(array_merge($feature->getTags(), $scenario->getTags(), $exampleTable->getTags()))) {
                        $exampleTables[] = $exampleTable;
                    }
                }

                $scenario = $scenario->withTables($exampleTables);
            }

            $scenarios[] = $scenario;
        }

        return $feature->withScenarios($scenarios);
    }

    public function isFeatureMatch(FeatureNode $feature): bool
    {
        return $this->evaluateTags($feature->getTags());
    }

    public function isScenarioMatch(FeatureNode $feature, ScenarioInterface $scenario): bool
    {
        if ($scenario instanceof OutlineNode && $scenario->hasExamples()) {
            foreach ($scenario->getExampleTables() as $example) {
                if ($this->evaluateTags(array_merge($feature->getTags(), $scenario->getTags(), $example->getTags()))) {
                    return true;
                }
            }

            return false;
        }

        return $this->evaluateTags(array_merge($feature->getTags(), $scenario->getTags()));
    }

    /**
     * @param array<array-key, string> $tags
     */
    private function evaluateTags(array $tags): bool
    {
        // tag expressions reference tags with their `@` prefix, but the legacy
        // parsing mode strips the prefix from the parsed nodes: add it back
        $tags = array_map(
            static fn (string $tag) => str_starts_with($tag, '@') ? $tag : '@' . $tag,
            $tags
        );

        return $this->expression->evaluate($tags);
    }
}
