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
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Context\SpecificationContext;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\RunControl;
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
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * Initializes tester.
     *
     * @param Tester             $scenarioTester
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(Tester $scenarioTester, EnvironmentManager $environmentManager)
    {
        $this->scenarioTester = $scenarioTester;
        $this->environmentManager = $environmentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function test(Context $context, RunControl $control)
    {
        $results = array();
        $context = $this->castContext($context);

        $feature = $this->extractFeatureNode($context);
        $environment = $context->getEnvironment();

        foreach ($feature->getScenarios() as $scenario) {
            $scenarioContext = new ScenarioContext($feature, $scenario, $environment);
            $scenarioContext = $scenarioContext->createIsolatedContext($this->environmentManager);
            $scenarioResult = $this->scenarioTester->test($scenarioContext, $control);
            $results[] = new IntegerTestResult($scenarioResult->getResultCode());
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
     * @param Context $context
     *
     * @return SpecificationContext
     *
     * @throws WrongContextException
     */
    private function castContext(Context $context)
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
