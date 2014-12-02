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
use Behat\Testwork\Tester\Context\TestContext;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\RunControl;
use Behat\Testwork\Tester\Tester;

/**
 * Mediates Gherkin scenario testing depending if it is a scenario or an outline.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class MediatingScenarioTester implements Tester
{
    /**
     * @var Tester
     */
    private $scenarioTester;
    /**
     * @var Tester
     */
    private $outlineTester;

    /**
     * Initializes context.
     *
     * @param Tester $scenarioTester
     * @param Tester $outlineTester
     */
    public function __construct(Tester $scenarioTester, Tester $outlineTester)
    {
        $this->scenarioTester = $scenarioTester;
        $this->outlineTester = $outlineTester;
    }

    /**
     * {@inheritdoc}
     */
    public function test(TestContext $context, RunControl $control)
    {
        $context = $this->castContext($context);

        if ($context->isOutline()) {
            return $this->outlineTester->test($context, $control);
        }

        return $this->scenarioTester->test($context, $control);
    }

    /**
     * Casts provided context to the expected one.
     *
     * @param TestContext $context
     *
     * @return ScenarioContext
     *
     * @throws WrongContextException
     */
    private function castContext(TestContext $context)
    {
        if ($context instanceof ScenarioContext) {
            return $context;
        }

        throw new WrongContextException(
            sprintf(
                'MediatingScenarioTester tests instances of ScenarioContext only, but %s given.',
                get_class($context)
            ), $context
        );
    }
}
