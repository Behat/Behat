<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Tests\Gherkin\Filter;

use Behat\Behat\Gherkin\Filter\TagExpressionFilter;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioNode;
use Cucumber\TagExpressions\TagExpressionException;
use PHPUnit\Framework\TestCase;

final class TagExpressionFilterTest extends TestCase
{
    public function testItThrowsOnInvalidExpression(): void
    {
        $this->expectException(TagExpressionException::class);

        new TagExpressionFilter('@a and (');
    }

    public function testItMatchesFeaturesByTag(): void
    {
        $filter = new TagExpressionFilter('@wip and not @slow');

        $this->assertTrue($filter->isFeatureMatch($this->createFeature(['wip'])));
        $this->assertFalse($filter->isFeatureMatch($this->createFeature(['wip', 'slow'])));
        $this->assertFalse($filter->isFeatureMatch($this->createFeature([])));
    }

    public function testScenariosInheritFeatureTags(): void
    {
        $filter = new TagExpressionFilter('@wip and @fast');

        $scenario = new ScenarioNode(null, ['fast'], [], 'Scenario', 2);
        $feature = $this->createFeature(['wip'], [$scenario]);

        $this->assertTrue($filter->isScenarioMatch($feature, $scenario));
        $this->assertFalse($filter->isScenarioMatch($this->createFeature([], [$scenario]), $scenario));
    }

    public function testItFiltersOutlineExampleTables(): void
    {
        $filter = new TagExpressionFilter('@fast or @quick');

        $matchingTable = new ExampleTableNode([10 => ['num'], 11 => ['1']], 'Examples', ['fast']);
        $otherTable = new ExampleTableNode([12 => ['num'], 13 => ['2']], 'Examples', ['slow']);
        $outline = new OutlineNode(null, [], [], [$matchingTable, $otherTable], 'Scenario Outline', 2);
        $feature = $this->createFeature([], [$outline]);

        $this->assertTrue($filter->isScenarioMatch($feature, $outline));

        $filteredFeature = $filter->filterFeature($feature);
        $filteredOutline = $filteredFeature->getScenarios()[0];

        $this->assertInstanceOf(OutlineNode::class, $filteredOutline);
        $this->assertSame([$matchingTable], $filteredOutline->getExampleTables());
    }

    public function testItAcceptsTagsWithExplicitAtPrefix(): void
    {
        $filter = new TagExpressionFilter('@wip');

        // tags are stored without the `@` prefix in legacy parsing mode, and with it in gherkin-32 mode
        $this->assertTrue($filter->isFeatureMatch($this->createFeature(['wip'])));
        $this->assertTrue($filter->isFeatureMatch($this->createFeature(['@wip'])));
    }

    /**
     * @param list<string> $tags
     * @param list<ScenarioNode|OutlineNode> $scenarios
     */
    private function createFeature(array $tags, array $scenarios = []): FeatureNode
    {
        return new FeatureNode(null, null, $tags, null, $scenarios, 'Feature', 'en', null, 1);
    }
}
