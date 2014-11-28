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
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\RunControl;
use Behat\Testwork\Tester\Tester;

/**
 * Tests provided Gherkin scenario.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ScenarioTester implements Tester
{
    /**
     * @var Tester
     */
    private $containerTester;
    /**
     * @var Tester
     */
    private $backgroundTester;
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * Initializes tester.
     *
     * @param Tester             $containerTester
     * @param Tester             $backgroundTester
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(
        Tester $containerTester,
        Tester $backgroundTester,
        EnvironmentManager $environmentManager
    ) {
        $this->containerTester = $containerTester;
        $this->backgroundTester = $backgroundTester;
        $this->environmentManager = $environmentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function test(Context $context, RunControl $control)
    {
        $results = array();
        $context = $this->castContext($context);

        $scenarioContext = $context->createIsolatedContext($this->environmentManager);
        $scenarioControl = clone $control;

        if ($scenarioContext->hasBackground()) {
            $backgroundContext = $scenarioContext->createBackgroundContext();
            $backgroundResult = $this->backgroundTester->test($backgroundContext, $scenarioControl);
            $results[] = new IntegerTestResult($backgroundResult->getResultCode());
        }

        $scenarioResult = $this->containerTester->test($scenarioContext, $scenarioControl);
        $results[] = new IntegerTestResult($scenarioResult->getResultCode());

        return new TestResults($results);
    }

    /**
     * Casts provided context to the expected one.
     *
     * @param Context $context
     *
     * @return ScenarioContext
     *
     * @throws WrongContextException
     */
    private function castContext(Context $context)
    {
        if ($context instanceof ScenarioContext) {
            return $context;
        }

        throw new WrongContextException(
            sprintf(
                'ScenarioTester tests instances of ScenarioContext only, but %s given.',
                get_class($context)
            ), $context
        );
    }
}
