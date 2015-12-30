<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Gherkin;

use Behat\Behat\Tester\Context\ScenarioContext;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Tester\Context\SpecificationContext;
use Behat\Testwork\Tester\Context\TestContext;
use Behat\Testwork\Tester\Control\RunControl;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Tester;

/**
 * Tests provided Gherkin feature.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FeatureTester implements Tester
{
    /**
     * @var Tester
     */
    private $scenarioTester;

    /**
     * Initializes tester.
     *
     * @param Tester $scenarioTester
     */
    public function __construct(Tester $scenarioTester)
    {
        $this->scenarioTester = $scenarioTester;
    }

    /**
     * {@inheritdoc}
     */
    public function test(TestContext $context, RunControl $control)
    {
        $results = array();
        $context = $this->castContext($context);
        $feature = $this->extractFeatureNode($context);
        $environment = $context->getEnvironment();

        foreach ($feature->getScenarios() as $scenario) {
            $scenarioContext = new ScenarioContext($feature, $scenario, $environment);
            $results[] = $this->scenarioTester->test($scenarioContext, $control);
        }

        return new TestResults($results);
    }

    /**
     * Extracts feature from the context.
     *
     * @param SpecificationContext $context
     *
     * @return FeatureNode
     *
     * @throws WrongContextException
     */
    private function extractFeatureNode(SpecificationContext $context)
    {
        $specification = $context->getSpecification();

        if ($specification instanceof FeatureNode) {
            return $specification;
        }

        throw new WrongContextException(
            sprintf(
                'FeatureTester expects a context holding an instance of FeatureNode, %s given.',
                get_class($specification)
            ), $context
        );
    }

    /**
     * Casts provided context to the expected one.
     *
     * @param TestContext $context
     *
     * @return SpecificationContext
     *
     * @throws WrongContextException
     */
    private function castContext(TestContext $context)
    {
        if ($context instanceof SpecificationContext) {
            return $context;
        }

        throw new WrongContextException(
            sprintf(
                'FeatureTester tests instances of SpecificationContext only, but %s given.',
                get_class($context)
            ), $context
        );
    }
}
